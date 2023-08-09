<?php

namespace App\Services\DiscountGroup;

use Illuminate\Http\Request;
use App\Models\DiscountGroup;

class DiscountGroupService
{
    public static function getAll(Request $request)
    {
        $discount_groups = $request->has("deleted") ?
            DiscountGroup::with('cards')->onlyTrashed()->paginate(DiscountGroup::getPaginationCount()) :
            DiscountGroup::with('cards')->paginate(DiscountGroup::getPaginationCount());
        return $discount_groups;
    }

    public static function getIsDeleted(Request $request)
    {
        $is_deleted = $request->has("deleted") ? true : false;
        return $is_deleted;
    }
}