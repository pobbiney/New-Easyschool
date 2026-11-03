<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\UserCat;
use App\Models\UserCatLink;
use App\Models\UserLink;
use App\Models\User;
use App\Models\UserAccessLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function getUserCategoriesView()
    {
        $categories = UserCat::orderBy('cat_name')->get();

        $userCounts = User::select('user_cat', DB::raw('count(*) as total'))
            ->groupBy('user_cat')
            ->pluck('total', 'user_cat');

        foreach ($categories as $category) {
            $category->assigned_links = UserCatLink::where('cat_id', $category->cat_id)
                ->join('user_links', 'user_cat_links.link_id', '=', 'user_links.link_id')
                ->where('user_links.link_url', '!=', '#')
                ->pluck('user_links.link_name')
                ->toArray();

            $category->users_count = (int) ($userCounts[$category->cat_id] ?? 0);
        }

        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('status', 'Active')->count(),
            'inactive' => $categories->where('status', '!=', 'Active')->count(),
            'users' => $userCounts->sum(),
        ];

        $parentLinks = UserLink::where('link_parent', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        $childLinks = UserLink::where('link_parent', '>', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        return view('user-management.user-categories', compact('categories', 'parentLinks', 'childLinks', 'stats'));
    }

    public function addUserCategory(Request $request)
    {
        $request->validate([
            'cat_name' => 'required|string|max:100',
            'status' => 'required',
            'link_ids' => 'nullable|array',
        ]);

        if (UserCat::where('cat_name', trim($request->cat_name))->count() > 0) {
            return back()->with('message_error', 'This category name already exists.');
        }

        $category = new UserCat();
        $category->cat_name = trim($request->cat_name);
        $category->status = trim($request->status);
        $category->save();

        $this->saveCategoryLinks($category->cat_id, $request->link_ids ?? []);

        return back()->with('message_success', 'User category added successfully.');
    }

    public function getUserCategoryId($id)
    {
        $category = UserCat::findOrFail($id);

        $assignedLinkIds = UserCatLink::where('cat_id', $id)
            ->pluck('link_id')
            ->toArray();

        $assignedLinks = UserCatLink::where('cat_id', $id)
            ->join('user_links', 'user_cat_links.link_id', '=', 'user_links.link_id')
            ->where('user_links.link_url', '!=', '#')
            ->where('user_links.status', 'Active')
            ->orderBy('user_links.link_name')
            ->get([
                'user_links.link_id',
                'user_links.link_name',
            ]);

        return response()->json([
            'cat_id' => $category->cat_id,
            'cat_name' => $category->cat_name,
            'status' => $category->status,
            'link_ids' => $assignedLinkIds,
            'links' => $assignedLinks,
        ]);
    }

    public function updateUserCategory(Request $request)
    {
        $request->validate([
            'cat_id' => 'required',
            'cat_name' => 'required|string|max:100',
            'status' => 'required',
            'link_ids' => 'nullable|array',
        ]);

        $category = UserCat::findOrFail($request->cat_id);

        $nameExists = UserCat::where('cat_name', trim($request->cat_name))
            ->where('cat_id', '!=', $request->cat_id)
            ->count();

        if ($nameExists > 0) {
            return back()->with('message_error', 'This category name already exists.');
        }

        $category->cat_name = trim($request->cat_name);
        $category->status = trim($request->status);
        $category->save();

        $this->saveCategoryLinks($category->cat_id, $request->link_ids ?? []);

        return back()->with('message_success', 'User category updated successfully.');
    }

    public function deleteUserCategory(Request $request)
    {
        $request->validate([
            'cat_id' => 'required',
        ]);

        $usersCount = User::where('user_cat', $request->cat_id)->count();

        if ($usersCount > 0) {
            return back()->with('message_error', 'Cannot delete. This category is assigned to ' . $usersCount . ' user(s).');
        }

        UserCatLink::where('cat_id', $request->cat_id)->delete();
        UserCat::where('cat_id', $request->cat_id)->delete();

        return back()->with('message_success', 'User category deleted successfully.');
    }

    public function getSystemAccessView(Request $request)
    {
        $staffMembers = Staff::query()
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();

        $logins = User::with('category')->get();
        $loginsByStaff = $logins->filter(fn ($user) => ! empty($user->staff_id))->keyBy('staff_id');
        $loginsByEmail = $logins->filter(fn ($user) => filled($user->email))
            ->keyBy(fn ($user) => strtolower(trim($user->email)));

        foreach ($staffMembers as $member) {
            $login = $loginsByStaff->get($member->id);

            if (! $login && filled($member->email)) {
                $login = $loginsByEmail->get(strtolower(trim($member->email)));
            }

            if ($login && $login->staff_id && (int) $login->staff_id !== (int) $member->id) {
                $login = null;
            }

            $member->setRelation('user', $login);
        }

        $selectedId = (int) ($request->query('staff') ?: old('staff_id'));
        $selectedStaff = $selectedId ? $staffMembers->firstWhere('id', $selectedId) : null;
        $staffUser = $selectedStaff?->user;
        $savedAccess = $this->savedAccessLinksForUser($staffUser);

        $categories = UserCat::where('status', 'Active')->orderBy('cat_name')->get();
        $parentLinks = UserLink::where('link_parent', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();
        $childLinks = UserLink::where('link_parent', '>', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        return view('user-management.system-access', [
            'staffMembers' => $staffMembers,
            'selectedStaff' => $selectedStaff,
            'categories' => $categories,
            'parentLinks' => $parentLinks,
            'childLinks' => $childLinks,
            'staffUser' => $staffUser,
            'assignedExtraLinkIds' => $savedAccess['ids'],
            'savedAccessLinks' => $savedAccess['links'],
        ]);
    }

    public function saveSystemAccess(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
        ]);

        $staff = Staff::findOrFail($request->staff_id);
        $staffUser = $this->findStaffLogin($staff);
        $enableLogin = $request->boolean('enable_system_access');

        if (! $enableLogin) {
            if ($staffUser && (int) $staffUser->id === (int) Auth::id()) {
                return back()->with('message_error', 'You cannot remove your own login from this page.');
            }

            if ($staffUser) {
                UserAccessLink::where('user_id', $staffUser->id)->delete();
                $staffUser->delete();
            }

            return redirect()
                ->route('system-access', ['staff' => $staff->id])
                ->with('message_success', 'System login removed for this staff member.');
        }

        $request->validate([
            'user_cat' => 'required',
            'password' => $staffUser ? 'nullable|string|min:8' : 'required|string|min:8',
            'extra_link_ids' => 'nullable|array',
        ]);

        if (blank($staff->email)) {
            return back()->withInput()->with('message_error', 'Add an email on the staff record before creating a login.');
        }

        $emailOwner = User::where('email', trim($staff->email))->first();
        $emailTakenBySomeoneElse = $emailOwner
            && (! $staffUser || $emailOwner->id !== $staffUser->id)
            && ($staffUser || ($emailOwner->staff_id && (int) $emailOwner->staff_id !== (int) $staff->id));

        if ($emailTakenBySomeoneElse) {
            return back()->withInput()->with('message_error', 'This email is already used by another login.');
        }

        $user = $staffUser ?: ($emailOwner ?: new User());
        $isNewUser = ! $user->exists;

        if ($isNewUser) {
            $user->password = Hash::make($request->password);
        } elseif ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->name = trim($staff->firstname.' '.($staff->othername ? $staff->othername.' ' : '').$staff->surname);
        $user->email = trim($staff->email);
        $user->phone = trim((string) $staff->mobile);
        $user->user_cat = $request->user_cat;
        $user->cat_id = $request->user_cat;
        $user->staff_id = $staff->id;
        $user->status = $staff->status ?: 'Active';
        $user->save();

        $this->saveUserAccessLinks($user->id, $request->extra_link_ids ?? []);

        return redirect()
            ->route('system-access', ['staff' => $staff->id])
            ->with('message_success', 'System access saved.');
    }

    private function saveCategoryLinks($catId, $selectedLinkIds)
    {
        UserCatLink::where('cat_id', $catId)->delete();

        $linkIdsToSave = [];

        foreach ($selectedLinkIds as $linkId) {
            $linkIdsToSave[] = (int) $linkId;

            $link = UserLink::find($linkId);

            if ($link && $link->link_parent > 0) {
                $linkIdsToSave[] = $link->link_parent;
            }
        }

        $linkIdsToSave = array_unique($linkIdsToSave);

        foreach ($linkIdsToSave as $linkId) {
            UserCatLink::create([
                'cat_id' => $catId,
                'link_id' => $linkId,
            ]);
        }
    }

    private function findStaffLogin(Staff $staff): ?User
    {
        $user = User::where('staff_id', $staff->id)->first();

        if ($user) {
            return $user;
        }

        if (blank($staff->email)) {
            return null;
        }

        $byEmail = User::where('email', trim($staff->email))->first();

        if ($byEmail && $byEmail->staff_id && (int) $byEmail->staff_id !== (int) $staff->id) {
            return null;
        }

        return $byEmail;
    }

    private function savedAccessLinksForUser(?User $staffUser): array
    {
        if (! $staffUser) {
            return [
                'ids' => [],
                'links' => [],
            ];
        }

        $links = UserAccessLink::query()
            ->where('user_access_links.user_id', $staffUser->id)
            ->join('user_links', 'user_access_links.link_id', '=', 'user_links.link_id')
            ->where('user_links.link_parent', '>', 0)
            ->where('user_links.link_url', '!=', '#')
            ->where('user_links.status', 'Active')
            ->orderBy('user_links.link_name')
            ->get([
                'user_links.link_id',
                'user_links.link_name',
            ]);

        return [
            'ids' => $links->pluck('link_id')->map(fn ($id) => (int) $id)->values()->all(),
            'links' => $links->map(fn ($link) => [
                'link_id' => (int) $link->link_id,
                'link_name' => $link->link_name,
            ])->values()->all(),
        ];
    }

    private function saveUserAccessLinks($userId, array $selectedLinkIds): void
    {
        UserAccessLink::where('user_id', $userId)->delete();

        $linkIdsToSave = [];

        foreach (array_unique(array_map('intval', $selectedLinkIds)) as $linkId) {
            $linkIdsToSave[] = $linkId;
            $link = UserLink::find($linkId);

            if ($link && $link->link_parent > 0) {
                $linkIdsToSave[] = (int) $link->link_parent;
            }
        }

        foreach (array_unique($linkIdsToSave) as $linkId) {
            UserAccessLink::create([
                'user_id' => $userId,
                'link_id' => $linkId,
            ]);
        }
    }
}
