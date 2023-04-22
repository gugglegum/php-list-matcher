<?php

declare(strict_types=1);

namespace gugglegum\ListMatcherTests;

use gugglegum\ListMatcher\ListMatcher;
use PHPUnit\Framework\TestCase;

class ListMatchTest extends TestCase
{
    private ListMatcher $listMatcher;

    public function setUp(): void
    {
        $this->listMatcher = new ListMatcher();
    }

    /**
     * Test for case when 2 lists are fully matched
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testAllMatched(callable $compare)
    {
        $list1 = [
            ['list1', 'one'],
            ['list1', 'two'],
            ['list1', 'three'],
        ];
        $list2 = [
            ['list2', 'three'],
            ['list2', 'one'],
            ['list2', 'two'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals(array_keys($list1), $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals($list1, $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals($list1, $this->listMatcher->getMatchedItemsInList1(true));

        $matchedKeys = $this->listMatcher->getMatchedKeysInList2();
        sort($matchedKeys);
        $this->assertSame(array_keys($list2), $matchedKeys);
        $this->assertEquals([$list2[1], $list2[2], $list2[0]], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals($list2, $this->listMatcher->getMatchedItemsInList2(true));

        $this->assertEquals([
            0 => 1,
            1 => 2,
            2 => 0,
        ], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([
            1 => 0,
            2 => 1,
            0 => 2,
        ], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([], $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(1, $this->listMatcher->getKey2MatchedToKey1(0));
        $this->assertEquals(2, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(0, $this->listMatcher->getKey2MatchedToKey1(2));

        $this->assertEquals(2, $this->listMatcher->getKey1MatchedToKey2(0));
        $this->assertEquals(0, $this->listMatcher->getKey1MatchedToKey2(1));
        $this->assertEquals(1, $this->listMatcher->getKey1MatchedToKey2(2));


        $this->assertEquals(['list2', 'one'], $this->listMatcher->getItem2MatchedToKey1(0));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(1));
        $this->assertEquals(['list2', 'three'], $this->listMatcher->getItem2MatchedToKey1(2));

        $this->assertEquals(['list1', 'three'], $this->listMatcher->getItem1MatchedToKey2(0));
        $this->assertEquals(['list1', 'one'], $this->listMatcher->getItem1MatchedToKey2(1));
        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(2));


        $this->assertTrue($this->listMatcher->isList1Matched());
        $this->assertTrue($this->listMatcher->isList2Matched());
        $this->assertTrue($this->listMatcher->isAllMatched());
    }


    /**
     * Test for case when not all items in the List #1 matched to items in the List #2
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testList1Partial(callable $compare)
    {
        $list1 = [
            ['list1', 'one'],
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'four'],
            ['list1', 'two'],
        ];
        $list2 = [
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'two'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals([1, 2, 4], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([
            1 => ['list1', 'two'],
            2 => ['list1', 'three'],
            4 => ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertEquals([0, 1, 2], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([
            0 => ['list2', 'two'],
            1 => ['list2', 'three'],
            2 => ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2(true));


        $this->assertEquals([
            1 => 0,
            2 => 1,
            4 => 2
        ], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([
            0 => 1,
            1 => 2,
            2 => 4
        ], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([
            ['list1', 'one'],
            ['list1', 'four'],
        ], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([
        ], $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(0));
        $this->assertEquals(0, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(1, $this->listMatcher->getKey2MatchedToKey1(2));
        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(3));
        $this->assertEquals(2, $this->listMatcher->getKey2MatchedToKey1(4));

        $this->assertEquals(1, $this->listMatcher->getKey1MatchedToKey2(0));
        $this->assertEquals(2, $this->listMatcher->getKey1MatchedToKey2(1));
        $this->assertEquals(4, $this->listMatcher->getKey1MatchedToKey2(2));


        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(0));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(1));
        $this->assertEquals(['list2', 'three'], $this->listMatcher->getItem2MatchedToKey1(2));
        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(3));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(4));

        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(0));
        $this->assertEquals(['list1', 'three'], $this->listMatcher->getItem1MatchedToKey2(1));
        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(2));


        $this->assertFalse($this->listMatcher->isList1Matched());
        $this->assertTrue($this->listMatcher->isList2Matched());
        $this->assertFalse($this->listMatcher->isAllMatched());
    }

    /**
     * Test for case when not all items in the List #2 matched to items in the List #1
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testList2Partial(callable $compare)
    {
        $list1 = [
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'four'],
            ['list1', 'two'],
        ];
        $list2 = [
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'four'],
            ['list2', 'five'],
            ['list2', 'one'],
            ['list2', 'two'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals([0, 1, 2, 3], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'four'],
            ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([
            0 => ['list1', 'two'],
            1 => ['list1', 'three'],
            2 => ['list1', 'four'],
            3 => ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertEquals([0, 1, 2, 5], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'four'],
            ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([
            0 => ['list2', 'two'],
            1 => ['list2', 'three'],
            2 => ['list2', 'four'],
            5 => ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2(true));


        $this->assertEquals([
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 5,
        ], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([
            0 => 0,
            1 => 1,
            2 => 2,
            5 => 3,
        ], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([
        ], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([
            ['list2', 'five'],
            ['list2', 'one'],
        ], $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(0, $this->listMatcher->getKey2MatchedToKey1(0));
        $this->assertEquals(1, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(2, $this->listMatcher->getKey2MatchedToKey1(2));
        $this->assertEquals(5, $this->listMatcher->getKey2MatchedToKey1(3));

        $this->assertEquals(0, $this->listMatcher->getKey1MatchedToKey2(0));
        $this->assertEquals(1, $this->listMatcher->getKey1MatchedToKey2(1));
        $this->assertEquals(2, $this->listMatcher->getKey1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(3));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(4));
        $this->assertEquals(3, $this->listMatcher->getKey1MatchedToKey2(5));


        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(0));
        $this->assertEquals(['list2', 'three'], $this->listMatcher->getItem2MatchedToKey1(1));
        $this->assertEquals(['list2', 'four'], $this->listMatcher->getItem2MatchedToKey1(2));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(3));

        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(0));
        $this->assertEquals(['list1', 'three'], $this->listMatcher->getItem1MatchedToKey2(1));
        $this->assertEquals(['list1', 'four'], $this->listMatcher->getItem1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(3));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(4));
        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(5));


        $this->assertTrue($this->listMatcher->isList1Matched());
        $this->assertFalse($this->listMatcher->isList2Matched());
        $this->assertFalse($this->listMatcher->isAllMatched());
    }

    /**
     * Test for case when not all items in both lists are matched, i.e. some items in both lists are not matched
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testBothPartialMatch(callable $compare)
    {
        $list1 = [
            ['list1', 'one'],
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'two'],
        ];
        $list2 = [
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'four'],
            ['list2', 'five'],
            ['list2', 'three'],
            ['list2', 'two'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals([1, 2, 3], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([
            1 => ['list1', 'two'],
            2 => ['list1', 'three'],
            3 => ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertEquals([0, 1, 5], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([
            0 => ['list2', 'two'],
            1 => ['list2', 'three'],
            5 => ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2(true));


        $this->assertEquals([
            1 => 0,
            2 => 1,
            3 => 5,
        ], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([
            0 => 1,
            1 => 2,
            5 => 3,
        ], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([
            ['list1', 'one'],
        ], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([
            ['list2', 'four'],
            ['list2', 'five'],
            ['list2', 'three'],
        ], $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(0));
        $this->assertEquals(0, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(1, $this->listMatcher->getKey2MatchedToKey1(2));
        $this->assertEquals(5, $this->listMatcher->getKey2MatchedToKey1(3));

        $this->assertEquals(1, $this->listMatcher->getKey1MatchedToKey2(0));
        $this->assertEquals(2, $this->listMatcher->getKey1MatchedToKey2(1));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(3));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(4));
        $this->assertEquals(3, $this->listMatcher->getKey1MatchedToKey2(5));


        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(0));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(1));
        $this->assertEquals(['list2', 'three'], $this->listMatcher->getItem2MatchedToKey1(2));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1(3));

        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(0));
        $this->assertEquals(['list1', 'three'], $this->listMatcher->getItem1MatchedToKey2(1));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(3));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(4));
        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2(5));


        $this->assertFalse($this->listMatcher->isList1Matched());
        $this->assertFalse($this->listMatcher->isList2Matched());
        $this->assertFalse($this->listMatcher->isAllMatched());
    }

    /**
     * Test for case when none of items are matched in both list, i.e. nothing matched
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testNoneMatch(callable $compare)
    {
        $list1 = [
            ['list1', 'one'],
            ['list1', 'three'],
            ['list1', 'five'],
        ];
        $list2 = [
            ['list2', 'two'],
            ['list2', 'four'],
            ['list2', 'six'],
            ['list2', 'seven'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals([], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertSame([], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList2(true));

        $this->assertEquals([], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals($list1, $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals($list2, $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(0));
        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(2));

        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(0));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(1));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(3));


        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(0));
        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(1));
        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(2));

        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(0));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(1));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(2));


        $this->assertFalse($this->listMatcher->isList1Matched());
        $this->assertFalse($this->listMatcher->isList2Matched());
        $this->assertFalse($this->listMatcher->isAllMatched());
    }


    /**
     * Test that empty lists is OK
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testEmptyLists(callable $compare)
    {
        $list1 = [];
        $list2 = [];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals([], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertSame([], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([], $this->listMatcher->getMatchedItemsInList2(true));

        $this->assertEquals([], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([], $this->listMatcher->getUnmatchedItemsInList2());

        // Test on non-existing keys
        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1(1));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2(2));
        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1(3));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2(4));


        $this->assertTrue($this->listMatcher->isList1Matched());
        $this->assertTrue($this->listMatcher->isList2Matched());
        $this->assertTrue($this->listMatcher->isAllMatched());
    }

    /**
     * Test case for associative arrays (based on testBothPartialMatch)
     *
     * @dataProvider compareFunctionsProvider
     * @param callable $compare
     * @return void
     */
    public function testAssoc(callable $compare)
    {
        $list1 = [
            'ONE' => ['list1', 'one'],
            'TWO' => ['list1', 'two'],
            'THREE' => ['list1', 'three'],
            'FOUR' => ['list1', 'two'],
        ];
        $list2 = [
            'ONE' => ['list2', 'two'],
            'TWO' => ['list2', 'three'],
            'THREE' => ['list2', 'four'],
            'FOUR' => ['list2', 'five'],
            'FIVE' => ['list2', 'three'],
            'SIX' => ['list2', 'two'],
        ];

        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($compare)
            ->match();

        $this->assertEquals(['TWO', 'THREE', 'FOUR'], $this->listMatcher->getMatchedKeysInList1());
        $this->assertEquals([
            ['list1', 'two'],
            ['list1', 'three'],
            ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1());
        $this->assertEquals([
            'TWO' => ['list1', 'two'],
            'THREE' => ['list1', 'three'],
            'FOUR' => ['list1', 'two'],
        ], $this->listMatcher->getMatchedItemsInList1(true));

        $this->assertEquals(['ONE', 'TWO', 'SIX'], $this->listMatcher->getMatchedKeysInList2());
        $this->assertEquals([
            ['list2', 'two'],
            ['list2', 'three'],
            ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2());
        $this->assertEquals([
            'ONE' => ['list2', 'two'],
            'TWO' => ['list2', 'three'],
            'SIX' => ['list2', 'two'],
        ], $this->listMatcher->getMatchedItemsInList2(true));


        $this->assertEquals([
            'TWO' => 'ONE',
            'THREE' => 'TWO',
            'FOUR' => 'SIX',
        ], $this->listMatcher->getKeyReferencesList1ToList2());
        $this->assertEquals([
            'ONE' => 'TWO',
            'TWO' => 'THREE',
            'SIX' => 'FOUR',
        ], $this->listMatcher->getKeyReferencesList2ToList1());


        $this->assertEquals([
            ['list1', 'one'],
        ], $this->listMatcher->getUnmatchedItemsInList1());
        $this->assertEquals([
            ['list2', 'four'],
            ['list2', 'five'],
            ['list2', 'three'],
        ], $this->listMatcher->getUnmatchedItemsInList2());


        $this->assertEquals(null, $this->listMatcher->getKey2MatchedToKey1('ONE'));
        $this->assertEquals('ONE', $this->listMatcher->getKey2MatchedToKey1('TWO'));
        $this->assertEquals('TWO', $this->listMatcher->getKey2MatchedToKey1('THREE'));
        $this->assertEquals('SIX', $this->listMatcher->getKey2MatchedToKey1('FOUR'));

        $this->assertEquals('TWO', $this->listMatcher->getKey1MatchedToKey2('ONE'));
        $this->assertEquals('THREE', $this->listMatcher->getKey1MatchedToKey2('TWO'));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2('THREE'));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2('FOUR'));
        $this->assertEquals(null, $this->listMatcher->getKey1MatchedToKey2('FIVE'));
        $this->assertEquals('FOUR', $this->listMatcher->getKey1MatchedToKey2('SIX'));


        $this->assertEquals(null, $this->listMatcher->getItem2MatchedToKey1('ONE'));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1('TWO'));
        $this->assertEquals(['list2', 'three'], $this->listMatcher->getItem2MatchedToKey1('THREE'));
        $this->assertEquals(['list2', 'two'], $this->listMatcher->getItem2MatchedToKey1('FOUR'));

        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2('ONE'));
        $this->assertEquals(['list1', 'three'], $this->listMatcher->getItem1MatchedToKey2('TWO'));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2('THREE'));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2('FOUR'));
        $this->assertEquals(null, $this->listMatcher->getItem1MatchedToKey2('FIVE'));
        $this->assertEquals(['list1', 'two'], $this->listMatcher->getItem1MatchedToKey2('SIX'));


        $this->assertFalse($this->listMatcher->isList1Matched());
        $this->assertFalse($this->listMatcher->isList2Matched());
        $this->assertFalse($this->listMatcher->isAllMatched());
    }

    public function testExceptionOnNoCompare()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Compare function must be defined before calling match()");
        $this->listMatcher
            ->match();
    }

    private function _initMatcher(): void
    {
        $list1 = [['list1', 'one']];
        $list2 = [['list2', 'one']];
        $this->listMatcher
            ->setList1($list1)
            ->setList2($list2)
            ->setCompare($this->compareFunctionsProvider()[0][0]);
    }
    public function testExceptionOnNotMatched_getMatchedKeysInList1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->assertEquals([1, 2, 3], $this->listMatcher->getMatchedKeysInList1());
    }
    public function testExceptionOnNotMatched_getMatchedItemsInList1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getMatchedItemsInList1();
    }
    public function testExceptionOnNotMatched_getMatchedItemsInList2()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getMatchedItemsInList2();
    }
    public function testExceptionOnNotMatched_getKeyReferencesList1ToList2()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getKeyReferencesList1ToList2();
    }
    public function testExceptionOnNotMatched_getKeyReferencesList2ToList1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getKeyReferencesList2ToList1();
    }
    public function testExceptionOnNotMatched_getUnmatchedItemsInList1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getUnmatchedItemsInList1();
    }
    public function testExceptionOnNotMatched_getUnmatchedItemsInList2()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getUnmatchedItemsInList2();
    }
    public function testExceptionOnNotMatched_getKey2MatchedToKey1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getKey2MatchedToKey1(0);
    }
    public function testExceptionOnNotMatched_getKey1MatchedToKey2()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getKey1MatchedToKey2(0);
    }
    public function testExceptionOnNotMatched_getItem2MatchedToKey1()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getItem2MatchedToKey1(0);
    }
    public function testExceptionOnNotMatched_getItem1MatchedToKey2()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->getItem1MatchedToKey2(0);
    }
    public function testExceptionOnNotMatched_isList1Matched()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->isList1Matched();
    }
    public function testExceptionOnNotMatched_isList2Matched()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->isList2Matched();
    }
    public function testExceptionOnNotMatched_isAllMatched()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The lists are not matched yet");
        $this->_initMatcher();
        $this->listMatcher->isAllMatched();
    }

    public function testGetters()
    {
        $this->_initMatcher();
        $this->assertEquals([['list1', 'one']], $this->listMatcher->getList1());
        $this->assertEquals([['list2', 'one']], $this->listMatcher->getList2());
        $this->assertEquals($this->compareFunctionsProvider()[0][0], $this->listMatcher->getCompare());
    }

    /**
     * Provider for 2 variants of compare function: 1) function that answers the question "is match?" and returns boolean;
     * 2) classic PHP compare function used in built-in sort functions which returns int(0) when items are equal, int(1)
     * when item#1 is greater than item#2 and int(-1) otherwise.
     *
     * @return \Closure[][]
     */
    public function compareFunctionsProvider(): array
    {
        return [
            [function($item1, $item2): bool {
                return $item1[1] == $item2[1];
            }],
            [function($item1, $item2): int {
                if ($item1[1] == $item2[1]) {
                    return 0;
                }
                return $item1[1] > $item2[1] ? 1 : -1;
            }],
        ];
    }

}
