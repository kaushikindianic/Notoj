<?php

/** @XX */
class xxyyzz
{
}

/**  @weird_alone */
function weirdo() {
}

/** @Foobar(@Foobar('foobar'), 'foobar') */
class extended extends xxyyzz
{
}

class parentxx extends extended
{
}

/** @Foo */
class xasdasda extends parentxx
{
}

/** @callable */
function something()
{
    return 1;
}

class something 
{
    /** @callable_method */
    public function something()
    {
        return 2;
    }

    /** @callable_method_static */
    public static function xxsomething()
    {
        return 3;
    }
}
