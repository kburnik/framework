#!/bin/bash

# Replace tabs with double space.
sed -i -e "s/\t/  /g" $@

# Remove \r.
sed -i -re "s/\r//g" $@

# Remove spaces before and after parens.
sed -i -re "s/\( */\(/g" $@
sed -i -re "s/ *\)/\)/g" $@

# Remove spaces before and after comma.
sed -i -re "s/ *,/,/g" $@
sed -i -re "s/, */,/g" $@

# Add single space after comma.
sed -i -re "s/,/, /g" $@

# Put left brace inline.
sed -i -re "N;/\n/s/\n *\{/ \{/g" $@

# Remove all double newline occurences.
tmp=$(mktemp)
for x in $@; do
  cp $x $tmp
  cat -s $tmp > $x
done;

# Compact brackets.
sed -i -re "s/\[ */\[/g" $@
sed -i -re "s/\ *]/\]/g" $@

# Remove space after !.
sed -i -re "s/\! +/\!/g" $@

# TODO: Make comments start with capital and end with .

# TODO: Remove empty lines after return.
