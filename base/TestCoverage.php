<?

class TestCoverage
{

  private static $count = 0 ;
  private function resetCounter()
  {
    self::$count = 0;
  }

  private function getNextCoverageCall()
  {
    $code = "/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,".self::$count.");/*</TestCoverage>*/";
    self::$count++;
    return $code;
  }

  private function wrapInCurlies( $code , $startPosition , $addition )
  {
    $openedCurly = "/*<TestCoverage>*/{/*</TestCoverage>*/";
    $closedCurly = "/*<TestCoverage>*/}/*</TestCoverage>*/";

    $code =
      substr( $code , 0 , $startPosition + 1  )
      . $openedCurly
      . substr($code,$startPosition+1)
      . $addition
      . $closedCurly
    ;


    return $code;

  }

  public function addCoverageCalls( $phpCode )
  {
    self::resetCounter();

    $tokens = token_get_all( $phpCode );

    // reset the states
    $parenLevel = 0;
    $curlyLevel = 0;

    foreach ($tokens as $token)
    {
       if (is_string($token))
       {
         // simple 1-character token
         $out .= $token;

         if ( $token == '{' )
         {

          if ( $inClass )
          {
            $inClassBody = true;

            $inClass = false;

            $classCurlyLevel = $curlyLevel;

          }


          if ( $inInterface )
          {
            $inInterfaceBody = true;

            $inInterface = false;

            $interfaceCurlyLevel = $curlyLevel;

          }


          if ( $inFunction )
          {
            $inFunctionBody = true;

            $inFunction = false;

            $functionCurlyLevel = $curlyLevel;

          }


          if ( $inBlockNakedBody )
          {
            $inBlockNakedBody = false;
          }

          $curlyLevel++;


         }
         else if ( $token == '}' )
         {

          $curlyLevel--;

          // echo "Curly closed\n";
          if ( $inClassBody && $classCurlyLevel == $curlyLevel )
          {
            $inClassBody = false;
          }

          if ( $inInterfaceBody && $interfaceCurlyLevel == $curlyLevel )
          {
            $inInterfaceBody = false;
          }

          if ( $inFunctionBody && $functionCurlyLevel == $curlyLevel )
          {
            $inFunctionBody = false;
          }



         }
         else if ( $token == '(' )
         {

          if ( $inBlock )
          {
            $inBlockHeader = true;

            $inBlock = false;

            $blockParenLevel = $parenLevel;
          }

          $parenLevel++;


         }
         else if ( $token == ')' )
         {

          $parenLevel--;

          if ( $inBlockHeader && $parenLevel == $blockParenLevel )
          {
            $inBlockHeader = false;

            $inBlockNakedBody = true;

            $blockBodyStartPosition = strlen( $out );
          }

         }


         // skip the coverage after the semicolon if in one of states:
         $skip = (

          ( $inClassBody && !$inFunctionBody )

          || $inInterfaceBody

          || $inAbstractDefinition

          || $inBlockHeader

          || $inReturnStatement

        );

         if ( $token == ';' )
         {

          if ( $inBlockNakedBody )
          {
            $inBlockNakedBody = false;

            $coverageCode = "";

            // maybe have to skip adding code because of return statement
            if ( !$skip )
              $coverageCode = self::getNextCoverageCall();

            $out = self::wrapInCurlies( $out , $blockBodyStartPosition , $coverageCode );

            continue;

          }

          if ( ! $skip )
          {
            $out .= self::getNextCoverageCall();
          }


          if ( $inAbstractDefinition )
          {
            $inAbstractDefinition = false;
          }

          if ( $inReturnStatement )
          {
            $inReturnStatement = false;
          }


         }


       }
       else
       {

        // token array
        list( $id, $text, $other ) = $token;

        // insert before the return token
        if ( $id == T_RETURN )
        {
          $inReturnStatement = true;
          $out .= self::getNextCoverageCall();
        }


         if ( $id == T_OPEN_TAG && ! $registerTagSet )
         {

          $firstOpenTag = $text;

          $registerTagSet = true;

          continue;
         }

         if ( $id == T_ABSTRACT )
         {
          $inAbstractDefinition = true;
         }

         if ( $id == T_CLASS )
         {
          $inClass = true;
         }

         if ( $id == T_INTERFACE )
         {
          $inInterface = true;
         }

         if ( $id == T_FUNCTION  )
         {
          $inFunction = true;
         }

         if ( in_array( $id , array( T_IF , T_ELSEIF , T_WHILE , T_FOR, T_FOREACH , T_CATCH ) ) )
         {
          $inBlock = true;
         }

         // after else assume in naked body, the curly opened brace will reset this state
         if ( in_array($id , array( T_ELSE , T_FINALLY , T_DO ) ) )
         {
          $inBlockNakedBody = true;
          $blockBodyStartPosition = strlen( $out ) + strlen( $text );
         }


         $out .= $text;


       }
    }

    if ( self::$count > 0 )
      $firstOpenTag .= "/*<TestCoverage>*/include_once('".__FILE__."'); TestCoverage::RegisterFile(__FILE__,".self::$count.");/*</TestCoverage>*/";


    return  $firstOpenTag . $out;
  }

  public function removeCoverageCalls( $phpCode )
  {

    $tagStart = '\/\*\<TestCoverage\>\*\/';
    $tagEnd = '\/\*\<\/TestCoverage\>\*\/';


    // (.*?) is ungreedy !! weeeee
    $pattern = '/('.$tagStart.')(.*?)+('.$tagEnd.')/ui';

    $phpCode = preg_replace($pattern,'',$phpCode);

    return $phpCode;

  }


  protected static $coverage = array();

  public static function RegisterFile( $filename , $count )
  {
    self::$coverage[ $file ][ 'count' ] = $count;
  }


  public static function Cover( $filename, $line, $index )
  {
    self::$coverage[ $file ][ 'covered' ][ $index ] = true;

  }


  public static function addCoverageCallsToFile( $file )
  {

    // omit self
    if ( realpath(__FILE__) === realpath( $file ) )
      return;

    $code = file_get_contents( $file );

    $code = self::removeCoverageCalls( $code );

    $coveredCode = self::addCoverageCalls( $code );

    file_put_contents( $file, $coveredCode );

  }


  public static function removeCoverageCallsFromFile( $file )
  {

    // omit self
    if ( realpath(__FILE__) === realpath( $file ) )
      return ;

    $code = file_get_contents( $file );

    $clearCode = self::removeCoverageCalls( $code );

    file_put_contents( $file, $clearCode );

  }


  public static function ShowResults()
  {

    foreach ( self::$coverage as $file => $res )
    {
      $count = $res[ 'count' ];
      $covered = $res[ 'covered' ];
      echo "$file: $covered / $count\r\n";
    }

  }


}




?>