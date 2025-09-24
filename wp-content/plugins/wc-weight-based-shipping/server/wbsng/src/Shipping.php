<?php declare(strict_types=1);

namespace Gzp\WbsNg;

use Gzp\WbsNg\Common\Equality\EqualityUtils;
use Gzp\WbsNg\Common\Set;
use Gzp\WbsNg\Model\Calc\Shipment;
use Gzp\WbsNg\Model\Calc\Solution;
use Gzp\WbsNg\Model\Config\Document;
use Gzp\WbsNg\Model\Config\Method;
use Gzp\WbsNg\Model\Order\Bundle;
use GzpWbsNgVendors\Dgm\Shengine\Model\Destination;
use function Gzp\WbsNg\Common\filter;


class Shipping
{
    /**
     * @return list<Solution>
     */
    public static function solutions(Bundle $items, ?Destination $dest, Document $document): array
    {
        if (!$document->active()) {
            return [];
        }

        $methods = filter($document->methods, function(Method $x) {
            return $x->active();
        });

        $solutions = [];
        foreach (self::solve($items, $dest, $methods, $document->settings->disableSplitShipping ? 1 : 3) as $shipments) {
            $solutions[] = new Solution(new Set($shipments));
        }

        $solutions = EqualityUtils::unique($solutions);

        $pos = [];
        foreach ($solutions as $idx => $solution) {
            $pos[spl_object_id($solution)] = $idx;
        }
        usort($solutions, static function(Solution $a, Solution $b) use ($pos): int {
            return
                [count($a->shipments), $pos[spl_object_id($a)]]
                <=>
                [count($b->shipments), $pos[spl_object_id($b)]];
        });

        return $solutions;
    }

    /**
     * Returns M!/(M-l)! solutions at max; l = min(M, L); M – number of methods, L – $maxMethodsInSolution.
     * Examples:
     *      M = 10, L = 3 => 720
     *      M = 10, L = 2 => 90
     *      M = 5,  L = 3 => 60
     *      M = 5,  L = 2 => 20
     *      M = 3,  L = 2 => 6
     *      M = 3,  L = 3 => 6
     *
     * @param Method[] $methods
     * @return list<list<Shipment>>
     */
    private static function solve(Bundle $bundle, ?Destination $dest, array $methods, int $maxShipmentsInSolution): array
    {
        if ($maxShipmentsInSolution < 1) {
            return [];
        }

        // A raw solution is a set of shipments
        $rawSolutions = [];
        foreach ($methods as $k => $method) {

            $shipment = $method->apply($bundle, $dest);
            if (!isset($shipment)) {
                continue;
            }

            $rest = $bundle->exclude($shipment->bundle);
            if ($rest->empty()) {
                $rawSolutions[] = [$shipment];
                continue;
            }

            $nextMethods = $methods;
            unset($nextMethods[$k]);

            $auxSolutions = self::solve($rest, $dest, $nextMethods, $maxShipmentsInSolution - 1);

            foreach ($auxSolutions as $auxSolution) {
                $rawSolutions[] = array_merge(
                    [$shipment], // keep the "main" method first
                    $auxSolution
                );
            }
        }

        return $rawSolutions;
    }
}