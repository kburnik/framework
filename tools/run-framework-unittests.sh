#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SRC=$(cd $DIR/.. && pwd)

cd $SRC
pwd
all_tests_passed=1
for testcase in $(find . -name "*TestCase.php"); do
  directory=$(dirname $testcase)
  basename=$(basename $testcase)

  # TODO(kburnik): Add blacklist instead.
  [ "$basename" == "TestCase.php" ] && continue
  [ "$basename" == "TestCoverageTestCase.php" ] && continue

  cd $SRC/$directory
  $DIR/testrun.php $@ -t $basename || all_tests_passed=0
done

[ "$all_tests_passed" != 1 ] && exit 1
exit 0
