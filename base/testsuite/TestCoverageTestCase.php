<?php

class TestCoverageTestCase extends TestCase {
  protected $coverage;


  public function __construct()
  {

    $this->coverage = new TestCoverage();


  }


  public function addCoverageCalls_simpleFunctionCall_addsCoverCalls()
  {

    $code = '<?php myfunc("hello worlds"); ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ myfunc("hello worlds");/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }


  public function addCoverageCalls_forLoopWithBlockOfCode_addsCoverCallsOnlyToBody()
  {

    $code = '<?php for( $i=0; $i < 5; $i++ ) { echo $i; }?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ for( $i=0; $i < 5; $i++ ) { echo $i;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ }?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }


  public function addCoverageCalls_forLoopWithParensInConditionAndBlockOfCode_addsCoverCallsOnlyToBody()
  {

    $code = '<?php for( $i=0; ($i < (5)) ; $i++ ) { echo $i; }?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ for( $i=0; ($i < (5)) ; $i++ ) { echo $i;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ }?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }



  public function addCoverageCalls_functionWithReturnStatement_returnStatementGetsPriorCoverageTag()
  {

    $code = '<?php
      function a()
      {
        return $x;
      }
    ?>';

    $expectedCode = '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/
      function a()
      {
        /*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/return $x;
      }
    ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }

  public function addCoverageCalls_classMembers_classMembersDontGetTagsAttached()
  {

    $code
    = '<?php
                        class MyCLS {

                                var $someval_1;
                                var $someval_2 = array();

                                private static $someval_3;
                                private static $someval_4 = array();

                                protected static $someval_5;
                                protected static $someval_6 = array();

                                public static $someval_7;
                                public static $someval_8 = array();


                                private $private_1;
                                private $private_2 = array();

                                protected $protected_1;
                                protected $protected_2 = array();

                                public static $public_1;
                                public static $public_2 = array();


                                function b()
                                {
                                        echo "b";
                                }

                                function a()
                                {
                                        return $x;
                                }
                        }
                ?>';

    $expectedCode
    = '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,2);/*</TestCoverage>*/
                        class MyCLS {

                                var $someval_1;
                                var $someval_2 = array();

                                private static $someval_3;
                                private static $someval_4 = array();

                                protected static $someval_5;
                                protected static $someval_6 = array();

                                public static $someval_7;
                                public static $someval_8 = array();


                                private $private_1;
                                private $private_2 = array();

                                protected $protected_1;
                                protected $protected_2 = array();

                                public static $public_1;
                                public static $public_2 = array();


                                function b()
                                {
                                        echo "b";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/
                                }

                                function a()
                                {
                                        /*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,1);/*</TestCoverage>*/return $x;
                                }
                        }
                ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }

  public function addCoverageCalls_ifStatementWithNoCodeBlock_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php if ( true ) echo "Truth"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ if ( true ) /*<TestCoverage>*/{/*</TestCoverage>*/echo "Truth";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }

  public function addCoverageCalls_ifStatementWithNoCodeBlockAndReturnStatement_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php if ( true ) return "Truth"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ if ( true ) /*<TestCoverage>*/{/*</TestCoverage>*//*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/return "Truth";/*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }


  public function addCoverageCalls_ifStatementWithNoCodeBlockAndQuotedParens_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php if ( ")))" != "x" ) echo "Truth"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ if ( ")))" != "x" ) /*<TestCoverage>*/{/*</TestCoverage>*/echo "Truth";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }

  public function addCoverageCalls_whileLoopNoCodeBlock_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php while ( true ) echo "Truth"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ while ( true ) /*<TestCoverage>*/{/*</TestCoverage>*/echo "Truth";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }


  public function addCoverageCalls_forLoopNoCodeBlock_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php for ( $i = 0; $i < 5; $i++ ) echo "$i"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ for ( $i = 0; $i < 5; $i++ ) /*<TestCoverage>*/{/*</TestCoverage>*/echo "$i";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }


  public function addCoverageCalls_foreachLoopNoCodeBlock_getsTurnedIntoBlockedStatementWithPrefixedTags()
  {
    $code = '<?php foreach ( array(1,2,3) as $val ) echo "$val"; ?>';

    $expectedCode= '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ foreach ( array(1,2,3) as $val ) /*<TestCoverage>*/{/*</TestCoverage>*/echo "$val";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );
  }


  private function __addRemoveAssertKeepsCodeUntouched( $code )
  {
    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $clearCode = $this->coverage->removeCoverageCalls( $coveredCode );

    $this->assertEqual( $code , $clearCode );

  }


  private function __assertKeepsCodeUntouched( $code )
  {
    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $code , $coveredCode );

  }

  public function addCovergeCalls_toInterface_keepsCodeUntouched()
  {

    $code = '<?php
      interface SampleInterface
      {

        public function foo();

        public function bar();

      }
    ?>';

    $this->__assertKeepsCodeUntouched( $code );

  }


  public function addCoverageCalls_toAbstractMethods_keepsCodeUnTouched()
  {
    $code = '<?php
      class SampleClassWithAbstractMethods
      {

        public abstract function foo();

        public abstract function bar();

      }
    ?>';

    $this->__assertKeepsCodeUntouched( $code );

  }


  public function addCoverageCalls_toEmptyMethods_keepsCodeUnTouched()
  {
    $code = '<?php
      class SampleClassWithEmptyMethods
      {

        public abstract function foo()
        {


        }

        public abstract function bar()
        {

        }

      }
    ?>';

    $this->__assertKeepsCodeUntouched( $code );

  }



  public function addCoverageCalls_toFileWithMultiplePHPtags_putsStartingCoverageOnlyAtFirstTag()
  {
    $code = '<?php ?> some text to output <?php echo "hi"; ?>';

    $expectedCode = '<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ ?> some text to output <?php echo "hi";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ ?>';

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );

  }


  public function addCoverageCalls_ifStatementWithElseAndNakedBodies_addsCurliesToBoth()
  {


$code='<?php
if (isset($parse_url["path"]))
$parse_url["path"] = rtrim(str_replace(basename($parse_url["path"]), "", $parse_url["path"]), "/") . "/" . ltrim($parts["path"], "/");
else
$parse_url["path"] = $parts["path"];
?>';


$expectedCode='<?php/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,2);/*</TestCoverage>*/
if (isset($parse_url["path"]))
/*<TestCoverage>*/{/*</TestCoverage>*/$parse_url["path"] = rtrim(str_replace(basename($parse_url["path"]), "", $parse_url["path"]), "/") . "/" . ltrim($parts["path"], "/");/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/
else
/*<TestCoverage>*/{/*</TestCoverage>*/$parse_url["path"] = $parts["path"];/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,1);/*</TestCoverage>*//*<TestCoverage>*/}/*</TestCoverage>*/
?>';


    $coveredCode = $this->coverage->addCoverageCalls( $code );

    $this->assertEqual( $expectedCode , $coveredCode );



  }


  public function addCoverageCalls_tryCatchFinally_addsCurlies()
  {


    $code='<?php?>';

    $expectedCode='<?php?>';


    $coveredCode = $this->coverage->addCoverageCalls( $code );

    // $this->assertEqual( $expectedCode , $coveredCode );

  }

  public function addCoverageCalls_doWhileLoop_addsCurlies()
  {


    $code='<?php?>';

    $expectedCode='<?php?>';


    $coveredCode = $this->coverage->addCoverageCalls( $code );

    // $this->assertEqual( $expectedCode , $coveredCode );

  }






  public function addCovergeCalls_sampleScript1_adheresTosample1Out()
  {

    $code = file_get_contents( dirname(__FILE__) .'/samples/sample1.php' );

    $coveredCode = $this->coverage->addCoverageCalls( $code );

    // file_put_contents( dirname(__FILE__) .'/samples/sample1.real.out.php' , $coveredCode );

    $expectedCode = file_get_contents( dirname(__FILE__) .'/samples/sample1.out.php' );

    $this->assertEqual( $expectedCode , $coveredCode );

  }


  public function addThenRemoveCovergeCalls_simpleFunctionCall_keepsCodeUntouched()
  {

    $code = '<?php myfunc("hello worlds"); ?>';

    $this->__addRemoveAssertKeepsCodeUntouched( $code );

  }

  public function addThenRemoveCovergeCalls_sampleScript1_keepsCodeUntouched()
  {

    $code = file_get_contents( dirname(__FILE__) .'/samples/sample1.php' );

    $this->__addRemoveAssertKeepsCodeUntouched( $code );

  }
}

