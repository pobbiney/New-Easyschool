<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AssessmentType;
use App\Models\ClassCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssessmentTypeController extends Controller
{
    public function index(Request $request)
    {
        $classCategories = ClassCategory::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $selectedCategoryId = $request->integer('class_category_id') ?: $classCategories->first()?->id;

        $assessmentTypes = AssessmentType::query()
            ->with('classCategory')
            ->addSelect(['assessments_count' => AssessmentType::usageCountSubquery()])
            ->when($selectedCategoryId, fn ($query) => $query->where('class_category_id', $selectedCategoryId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('settings.assessment-types', [
            'assessmentTypes' => $assessmentTypes,
            'classCategories' => $classCategories,
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_category_id' => 'required|exists:class_categories,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assessment_types', 'name')->where(
                    fn ($query) => $query->where('class_category_id', $request->class_category_id)
                ),
            ],
            'category' => 'required|in:'.implode(',', array_keys(AssessmentType::CATEGORIES)),
            'sort_order' => 'required|integer|min:1|max:99',
            'max_number' => 'required|integer|min:1|max:999',
            'status' => 'required|in:Active,Inactive',
        ]);

        $classCategoryId = (int) $request->class_category_id;
        $name = trim($request->name);

        $type = new AssessmentType();
        $type->class_category_id = $classCategoryId;
        $type->name = $name;
        $type->slug = AssessmentType::makeUniqueSlug($name, $classCategoryId);
        $type->category = trim($request->category);
        $type->sort_order = (int) $request->sort_order;
        $type->max_number = (int) $request->max_number;
        $type->status = trim($request->status);
        $type->created_by = Auth::id();
        $type->save();

        return redirect()
            ->route('assessment-types', ['class_category_id' => $classCategoryId])
            ->with('message_success', 'Assessment type added successfully.');
    }

    public function show($id)
    {
        return response()->json(AssessmentType::with('classCategory')->findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'class_category_id' => 'required|exists:class_categories,id',
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', array_keys(AssessmentType::CATEGORIES)),
            'sort_order' => 'required|integer|min:1|max:99',
            'max_number' => 'required|integer|min:1|max:999',
            'status' => 'required|in:Active,Inactive',
        ]);

        $type = AssessmentType::findOrFail($request->assessment_type_id);
        $classCategoryId = (int) $request->class_category_id;
        $name = trim($request->name);

        if ($type->isInUse() && $classCategoryId !== (int) $type->class_category_id) {
            return back()->with('message_error', 'Class category cannot be changed because this type is already in use.');
        }

        $nameExists = AssessmentType::query()
            ->where('class_category_id', $classCategoryId)
            ->where('name', $name)
            ->where('id', '!=', $type->id)
            ->exists();

        if ($nameExists) {
            return back()->with('message_error', 'This assessment type already exists for the selected class category.');
        }

        $type->class_category_id = $classCategoryId;
        $type->name = $name;
        $type->category = trim($request->category);
        $type->sort_order = (int) $request->sort_order;
        $type->max_number = (int) $request->max_number;
        $type->status = trim($request->status);
        $type->updated_by = Auth::id();
        $type->save();

        return redirect()
            ->route('assessment-types', ['class_category_id' => $classCategoryId])
            ->with('message_success', 'Assessment type updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'assessment_type_id' => 'required|exists:assessment_types,id',
        ]);

        $type = AssessmentType::findOrFail($request->assessment_type_id);
        $categoryId = $type->class_category_id;

        if ($type->isInUse()) {
            $count = $type->usageQuery()->count();

            return back()->with(
                'message_error',
                'This assessment type cannot be deleted because it is already used by '.$count.' assessment(s).'
            );
        }

        $type->delete();

        return redirect()
            ->route('assessment-types', array_filter(['class_category_id' => $categoryId]))
            ->with('message_success', 'Assessment type deleted successfully.');
    }
}
