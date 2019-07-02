<?php declare(strict_types=1);

namespace Helper;

use Codeception\Util\JsonArray;

trait StoreTrait
{
    /**
     * @var string[]
     */
    private $store = [];

    /**
     * @param string $path
     * @param string $nameInStore
     */
    public function storeIdAs(string $path, string $nameInStore): void
    {
        $json = new JsonArray($this->grabResponse());
        $value = $json->filterByJsonPath($path)[0] ?? null;

        if ($value === null) {
            throw new \Exception('"' . $path . '" not found in the JSON body: ' . $this->grabResponse());
        }

        $this->store($value, $nameInStore);
        $this->debug('Stored id "' . $this->store[$nameInStore] . '" as "' . $path . '"');
    }

    public function store(string $value, string $nameInStore): void
    {
        if (isset($this->store[$nameInStore])) {
            throw new \Exception('"' . $nameInStore . '" is already in store, choose another name');
        }

        $this->store[$nameInStore] = $value;
    }

    /**
     * @param string $nameInStore
     * @param bool $raiseException
     *
     * @return null|string
     */
    public function getPreviouslyStoredIdOf(string $nameInStore, bool $raiseException = true): ?string
    {
        if (!isset($this->store[$nameInStore])) {
            if (!$raiseException) {
                return null;
            }

            throw new \Exception('"' . $nameInStore . '" is not present in the store');
        }

        return $this->store[$nameInStore];
    }

    public function deleteFromStore(string $nameInStore): void
    {
        unset($this->store[$nameInStore]);
    }

    private function clearTheStore(): void
    {
        $this->debug('Clearing the store');

        $this->store = [];
    }
}
