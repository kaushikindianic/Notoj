<?php
use Notoj\ReflectionClass,
    Notoj\ReflectionObject,
    Notoj\ReflectionFunction,
    Notoj\ReflectionMethod;

/** @zzexpect(True) */
function someFunction() {
}

/** @invalid_me*/
// foo
function foo() {
}

/** @test */
class simpletest extends \phpunit_framework_testcase
{
    /** @var_name("foo") */
    protected $bar;

    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @zzexpect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testClass()
    {
        $reflection = new ReflectionClass($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'test');
        $this->assertEquals($annotation[0]['args'], NULL);
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testClass') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
                $this->assertEquals($annotation[0]['method'], 'zzexpect');
                $this->assertEquals($annotation[0]['args'][0], true);
                $this->assertequals($annotation[1]['method'], 'bar');
                $this->assertEquals($annotation[1]['args'][0], false);
                $this->assertequals($annotation[2]['method'], 'bar');
                $this->assertEquals($annotation[2]['args'][0], 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]['method'], 'var_name');
                $this->assertEquals($annotation[0]['args'][0], 'foo');
            }
        }
    }
    /* }}} */

    public function testFunction() {
        $function   = new ReflectionFunction('someFunction');
        $annotation = $function->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'zzexpect');
        $this->assertEquals($annotation[0]['args'][0], true);
    }

    /** yet another comment {{{
     * This is a bloody comment that nobody is going to read
     *
     * @zzexpect(True) 
     * @bar(False)
     * @bar hola que tal?
     */
    function testObject() 
    {
        $reflection = new ReflectionObject($this);
        $annotation = $reflection->getAnnotations();
        $this->assertEquals(1, count($annotation));
        $this->assertEquals($annotation[0]['method'], 'test');
        $this->assertEquals($annotation[0]['args'], NULL);
        
        foreach ($reflection->getMethods() as $method) {
            $this->assertTrue($method instanceof \Notoj\ReflectionMethod);
            if ($method->getName() == 'testObject') {
                $annotation = $method->getAnnotations();
                $this->assertEquals(3, count($annotation));
                $this->assertEquals($annotation[0]['method'], 'zzexpect');
                $this->assertEquals($annotation[0]['args'][0], true);
                $this->assertequals($annotation[1]['method'], 'bar');
                $this->assertEquals($annotation[1]['args'][0], false);
                $this->assertequals($annotation[2]['method'], 'bar');
                $this->assertEquals($annotation[2]['args'][0], 'hola que tal?');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property instanceof \Notoj\ReflectionProperty);
            if ($property->getName() === 'bar') {
                $annotation = $property->getAnnotations();
                $this->assertEquals($annotation[0]['method'], 'var_name');
                $this->assertEquals($annotation[0]['args'][0], 'foo');
            }
        }
    }
    /* }}} */

    /** @test( dasda @bar) */
    function testError() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, count($annotations));
    }

    function testNoAnnotations() {
        $annotations = getReflection(__METHOD__)->getAnnotations();
        $this->assertEquals(0, count($annotations));
    }

    function testNotojParseFiles() {
        $notoj = new \Notoj\Notoj;
        $annotations = $notoj->parseFile(__FILE__);
        $this->assertTrue(is_array($annotations));
        $this->assertTrue(count($annotations) >= 4);
        $hasProperty = false;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ReflectionClass) {
                $this->AssertEquals($annotation->getName(), __CLASS__);
            } else if ($annotation instanceof ReflectionMethod) {
                $name = $annotation->getName();
                $this->assertTrue(is_callable(array($this, $name)));
            } else if ($annotation instanceof ReflectionProperty) {
                $hasProperty = true;
            }
        }
        $this->assertTrue($hasProperty);
    }

    public function testNotojQuery()
    {
        \Notoj\Notoj::parseAll();
        $annotations = \Notoj\Notoj::query('zzexpect');
        $this->assertEquals(count($annotations), 3);
    }


    public static function fileProvider() 
    {
        $args = array();
        foreach (glob(__DIR__ . "/../lib/Notoj/*.php") as $file) {
            $args[] = array($file);
        }
        return $args;
    }

    /**
     *  @dataProvider fileProvider
     */
    public function testNotojFile($file) 
    {
        $obj = new \Notoj\File($file);
        foreach ($obj->getAnnotations() as $annotations) {
            if (isset($annotations['function']) && isset($annotations['class'])) {
                $refl = new ReflectionMethod($annotations['class'], $annotations['function']);
            } else if (isset($annotations['class'])) {
                $refl = new ReflectionClass($annotations['class']);
            } else if (isset($annotations['function'])) {
                die("I'm not implemented yet!");
            }

            $this->assertEquals($refl->getAnnotations(), $annotations['annotations']);
        }
    }

    public function testNotojFileInvalid()
    {
        $foo = new \Notoj\File(__FILE__);
        foreach ($foo->getAnnotations() as $annotations) {
            foreach ($annotations['annotations'] as $annotation) {
                $this->assertNotEquals($annotation['method'], 'invalid_me');
            }
        }
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testNotojFileNotFound()
    {
        new \Notoj\File(__DIR__ . "/fixtures/not-found.php");
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testNotojDirNotFound()
    {
        new \Notoj\File(__DIR__ . "/fixtures/not-found/");
    }

    public function testNotojDir() 
    {
        $foo = new \Notoj\Dir(__DIR__ . '/fixtures');
        $foo->getAnnotations();
    }

    public function testNotojFileNamespaces() 
    {
        $foo = new \Notoj\File(__DIR__ . "/fixtures/namespace.php");
        foreach ($foo->getAnnotations() as $id => $annotation) {
            if ($id < 2) {
                $expected = explode("\\", isset($annotation['class']) ? $annotation['class'] : $annotation['function']);
                $expected = array_pop($expected);
            } else {
                $expected = isset($annotation['class']) ? $annotation['class'] : $annotation['function'];
            }
            foreach ($annotation['annotations'] as $ann) {
                $this->assertEquals($ann['method'], $expected);
            }
        }
    }
}
