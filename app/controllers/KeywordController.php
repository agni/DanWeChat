<?php
/**
 * User: yz.chen
 * Time: 2018-07-25 14:54
 */

namespace Dandelion\Controllers;

use Dandelion\Models\Keyword;

class KeywordController extends ControllerBase
{
    public function listAction($category)
    {
        $category = str_replace("-", "_", $category);
        $filter[] = "status > 0";
        if ("all" !== $category) {
            $filter[] = "category = \"$category\"";
        }
        return $this->sendSuccess(Keyword::find([
            "conditions" => implode(" AND ", $filter),
            "columns"    => ["id", "name", "category"],
        ]));
    }
}
