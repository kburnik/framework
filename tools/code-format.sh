#!/bin/bash

# Temporary for all in/out.
tmp=$(mktemp)

# Replace tabs with double space.
sed -i -e "s/\t/  /g" $@

# Replace \r with \n. \n\n will be replaced by cat.
sed -i -re "s/\r/\n/g" $@

# Remove spaces before and after parens.
sed -i -re "s/\( */\(/g" $@
sed -i -re "s/ *\)/\)/g" $@

# Remove spaces before and after comma.
sed -i -re "s/ *,/,/g" $@
sed -i -re "s/, */,/g" $@

# Add single space after comma.
sed -i -re "s/,/, /g" $@

for x in $@; do
  # Remove all double newline occurences.
  cp $x $tmp && cat -s $tmp > $x

  # Put left brace inline.
  number_of_occurrences=$(grep -o "\{" < $x | wc -l)
  for y in $(seq 1 $number_of_occurrences); do
    sed -i -re "N;/\n/s/\n *\{/ \{/g" $x
  done;
done;

# Compact brackets.
sed -i -re "s/\[ */\[/g" $@
sed -i -re "s/\ *]/\]/g" $@

# Remove space after !.
sed -i -re "s/\! +/\!/g" $@

# TODO: Make comments start with capital and end with .

# TODO: Remove empty lines after return.

# TODO: single spaces between concat . operator

# TODO: else in format } else {

# TODO: newline before break, continue, return

# Clean up.
rm $tmp