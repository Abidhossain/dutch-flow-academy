<?php declare(strict_types=1);

namespace Gzp\WbsNg\Model\Calc;

use Gzp\WbsNg\Common\Decimal;
use Gzp\WbsNg\Common\Equality\Equality;
use Gzp\WbsNg\Common\Equality\Traits\ImmutableHash;
use Gzp\WbsNg\Common\Equality\Traits\StandardEquality;
use Gzp\WbsNg\Common\Hashing\OrderedHash;
use Gzp\WbsNg\Mapping\Context;
use Gzp\WbsNg\Mapping\T;
use Gzp\WbsNg\Model\Config\Method;
use Gzp\WbsNg\Model\Order\Bundle;


/**
 * @immutable
 */
class Shipment implements Equality
{
    use StandardEquality;
    use ImmutableHash;
    use ShipmentSerialization;


    /**
     * @var string
     */
    public $title;

    /**
     * @var Decimal
     */
    public $price;

    /**
     * @var Bundle
     */
    public $bundle;

    /**
     * Method is null after deserialization and is not used in comparison.
     *
     * @var ?Method
     */
    public $method;


    public function __construct(string $title, Decimal $price, Bundle $items, Method $method = null)
    {
        assert(!$items->empty(), 'shipment package must not be empty');
        $this->title = $title;
        $this->price = $price;
        $this->bundle = $items;
        $this->method = $method;
    }

    protected function _equals(self $to): bool
    {
        return
            $this->title === $to->title &&
            $this->price->equals($to->price) &&
            $this->bundle->equals($to->bundle);
    }

    protected function _hash(): int
    {
        return OrderedHash::from(
            $this->title,
            $this->price,
            $this->bundle
        );
    }
}


trait ShipmentSerialization
{
    public function serialize(): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price->__toString(),
            'bundle' => $this->bundle->serialize(),
        ];
    }

    public static function unserialize(array $data): self
    {
        $data = Context::of($data);
        return new self(
            $data['title']->map([T::class, 'string']),
            $data['price']->map([T::class, 'decimal']),
            $data['bundle']->map([Bundle::class, 'unserialize']),
            null
        );
    }
}