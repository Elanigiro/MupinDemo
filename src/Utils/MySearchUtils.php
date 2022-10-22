<?php
declare(strict_types=1);
namespace App\Utils;

class MySearchUtils {

    public static function processString(string $searchtxt): string {

        return (preg_replace('/(\w\b)\W*/m', '$1* ', $searchtxt)) ?? '';
    }

    /** It updates $itemsFound by incrementing the relevance for the item AND returns $tagsFound after duplicates removal */
    public static function processItemsAndTags(array $itemsFound, array $tagsFound): array {

        $updater = function ($x) use ($itemsFound) {
            if (isset($itemsFound[$x->inv_id])) {
                $itemsFound[$x->inv_id]->relevance += $x->relevance;
                return false;
            }
            return true;
        };

        return array_filter($tagsFound, $updater);
    }

    public static function applyRelevanceToItems(array $itemsMap, array $inventoryItems): void {

        foreach ($inventoryItems as $invItem) {

            if (isset($itemsMap[$invItem->inv_id])) {

                $itemsMap[$invItem->inv_id]->relevance = $invItem->relevance;
            }
        }
    }
}