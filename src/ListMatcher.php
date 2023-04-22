<?php

declare(strict_types=1);

namespace gugglegum\ListMatcher;

/**
 * Universal callback-powered item matcher for 2 different arrays
 */
class ListMatcher
{
    /** @var array List #1 */
    private array $list1 = [];

    /** @var array List #2 */
    private array $list2 = [];

    /** @var callable */
    private $compare;

    private bool $isMatched = false;
    private array $refList1ToList2 = [];
    private array $refList2ToList1 = [];

    /**
     * @return array
     */
    public function getList1(): array
    {
        return $this->list1;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setList1(array $list): self
    {
        $this->list1 = $list;
        $this->clearRefs();
        return $this;
    }

    /**
     * @return array
     */
    public function getList2(): array
    {
        return $this->list2;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setList2(array $list): self
    {
        $this->list2 = $list;
        $this->clearRefs();
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getCompare(): ?callable
    {
        return $this->compare;
    }

    /**
     * The compare callable that accepts 2 arguments (item from the List #1 and item from the List #2) and returns TRUE or int(0) when these items are match each other
     *
     * @param callable $compare
     * @return $this
     */
    public function setCompare(callable $compare): self
    {
        $this->compare = $compare;
        return $this;
    }

    /**
     * Makes a match of items of 2 lists and builds internal indexes
     *
     * @return self
     */
    public function match(): self
    {
        if (!$this->compare) {
            throw new \LogicException("Compare function must be defined before calling match()");
        }
        foreach ($this->list1 as $key1 => $item1) {
            foreach ($this->list2 as $key2 => $item2) {
                if (isset($this->refList2ToList1[$key2])) {
                    continue;
                }
                $result = call_user_func($this->compare, $item1, $item2);
                if ($result === true || $result === 0) {
                    $this->refList1ToList2[$key1] = $key2;
                    $this->refList2ToList1[$key2] = $key1;
                    break;
                }
            }
        }
        $this->isMatched = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getKeyReferencesList1ToList2(): array
    {
        $this->checkIsMatched();
        return $this->refList1ToList2;
    }

    /**
     * @return array
     */
    public function getKeyReferencesList2ToList1(): array
    {
        $this->checkIsMatched();
        return $this->refList2ToList1;
    }

    /**
     * @return array
     */
    public function getMatchedKeysInList1(): array
    {
        $this->checkIsMatched();
        return array_keys($this->refList1ToList2);
    }

    /**
     * @return array
     */
    public function getMatchedKeysInList2(): array
    {
        $this->checkIsMatched();
        return array_keys($this->refList2ToList1);
    }

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function getMatchedItemsInList1(bool $preserveKeys = false): array
    {
        $this->checkIsMatched();
        $result = [];
        foreach ($this->refList2ToList1 as $key1) {
            if ($preserveKeys) {
                $result[$key1] = $this->list1[$key1];
            } else {
                $result[] = $this->list1[$key1];
            }
        }
        return $result;
    }

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function getMatchedItemsInList2(bool $preserveKeys = false): array
    {
        $this->checkIsMatched();
        $result = [];
        foreach ($this->refList1ToList2 as $key2) {
            if ($preserveKeys) {
                $result[$key2] = $this->list2[$key2];
            } else {
                $result[] = $this->list2[$key2];
            }
        }
        return $result;
    }

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function getUnmatchedItemsInList1(bool $preserveKeys = false): array
    {
        $this->checkIsMatched();
        $result = [];
        foreach ($this->list1 as $key1 => $item1) {
            if (!array_key_exists($key1, $this->refList1ToList2)) {
                if ($preserveKeys) {
                    $result[$key1] = $item1;
                } else {
                    $result[] = $item1;
                }
            }
        }
        return $result;
    }

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function getUnmatchedItemsInList2(bool $preserveKeys = false): array
    {
        $this->checkIsMatched();
        $result = [];
        foreach ($this->list2 as $key2 => $item2) {
            if (!array_key_exists($key2, $this->refList2ToList1)) {
                if ($preserveKeys) {
                    $result[$key2] = $item2;
                } else {
                    $result[] = $item2;
                }
            }
        }
        return $result;
    }

    /**
     * @param int|string $item1Key
     * @return int|string|null
     */
    public function getKey2MatchedToKey1(int|string $item1Key): int|string|null
    {
        $this->checkIsMatched();
        return array_key_exists($item1Key, $this->refList1ToList2) ? $this->refList1ToList2[$item1Key] : null;
    }

    /**
     * @param int|string $item2Key
     * @return int|string|null
     */
    public function getKey1MatchedToKey2(int|string $item2Key): int|string|null
    {
        $this->checkIsMatched();
        return array_key_exists($item2Key, $this->refList2ToList1) ? $this->refList2ToList1[$item2Key] : null;
    }

    /**
     * @param int|string $item1Key
     * @return mixed
     */
    public function getItem2MatchedToKey1(int|string $item1Key): mixed
    {
        $this->checkIsMatched();
        return array_key_exists($item1Key, $this->refList1ToList2) ? $this->list2[$this->refList1ToList2[$item1Key]] : null;
    }

    /**
     * @param int|string $item2Key
     * @return mixed
     */
    public function getItem1MatchedToKey2(int|string $item2Key): mixed
    {
        $this->checkIsMatched();
        return array_key_exists($item2Key, $this->refList2ToList1) ? $this->list1[$this->refList2ToList1[$item2Key]] : null;
    }

    /**
     * Check if all items in List #1 matched to some items in List #2
     *
     * @return bool
     */
    public function isList1Matched(): bool
    {
        $this->checkIsMatched();
        foreach ($this->list1 as $key1 => $item1) {
            if (!array_key_exists($key1, $this->refList1ToList2)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all items in List #2 matched to some items in List #1
     *
     * @return bool
     */
    public function isList2Matched(): bool
    {
        $this->checkIsMatched();
        foreach ($this->list2 as $key2 => $item2) {
            if (!array_key_exists($key2, $this->refList2ToList1)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all items in List #1 matched to items in List #2 and all items in List #2 are matched to items in List #1,
     * i.e. no unmatched items in both lists.
     *
     * @return bool
     */
    public function isAllMatched(): bool
    {
        return $this->isList1Matched() && $this->isList2Matched();
    }

    /**
     * @return void
     */
    private function clearRefs(): void
    {
        $this->refList2ToList1 = [];
        $this->refList1ToList2 = [];
        $this->isMatched = false;
    }

    /**
     * @return void
     */
    private function checkIsMatched(): void
    {
        if (!$this->isMatched) {
            throw new \LogicException("The lists are not matched yet");
        }
    }
}
