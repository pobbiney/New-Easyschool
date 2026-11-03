<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAccessService
{
    /** @var array<int, list<string>> */
    private array $urlCache = [];

    /**
     * @return list<string>
     */
    public function urls(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return [];
        }

        $userId = (int) $user->id;
        if (isset($this->urlCache[$userId])) {
            return $this->urlCache[$userId];
        }

        $linkIds = DB::table('user_access_links')
            ->where('user_id', $userId)
            ->pluck('link_id');

        $categoryId = (int) ($user->user_cat ?: $user->cat_id);
        if ($categoryId) {
            $categoryLinkIds = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->pluck('link_id');
            $linkIds = $linkIds->merge($categoryLinkIds);
        }

        $leaveLinkId = DB::table('user_links')
            ->where('link_url', 'leave-application')
            ->where('status', 'Active')
            ->value('link_id');

        if ($leaveLinkId) {
            $linkIds = $linkIds->push((int) $leaveLinkId);
        }

        if ($linkIds->isEmpty()) {
            return $this->urlCache[$userId] = [];
        }

        $parentIds = DB::table('user_links')
            ->whereIn('link_id', $linkIds)
            ->where('link_parent', '>', 0)
            ->pluck('link_parent');

        $allIds = $linkIds->merge($parentIds)->unique()->values();

        $urls = DB::table('user_links')
            ->whereIn('link_id', $allIds)
            ->where('status', 'Active')
            ->whereNotNull('link_url')
            ->where('link_url', '!=', '')
            ->pluck('link_url')
            ->unique()
            ->values()
            ->all();

        return $this->urlCache[$userId] = $urls;
    }

    public function can(string $routeName, ?User $user = null): bool
    {
        return in_array($routeName, $this->urls($user), true);
    }

    /**
     * @param  list<string>  $routeNames
     */
    public function canAny(array $routeNames, ?User $user = null): bool
    {
        foreach ($routeNames as $routeName) {
            if ($this->can($routeName, $user)) {
                return true;
            }
        }

        return false;
    }
}
