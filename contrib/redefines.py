#!/usr/bin/env python3
# Author: Tyson Andre
import re
import sys

# assumes file names don't have spaces.
# Could rewrite all of this to use JSON.
REDEFINE_REGEX=re.compile(r'^\S+:\d+ PhanRedefine\w+ .*defined at (\S*):\d+.*at (\S+):\d+$')
assert(REDEFINE_REGEX.match(r'aaXX/fy.php:571 PhanRedefineFunction Function error defined at aaXX/fy.php:571 was previously defined at blah/law.inc:193'))

def compare_files(a, b):
    '''Can be customized. If this returns true, a is preferred to be kept over b'''
    return a < b

def get_redefine_pairs(filename):
    fin = open(filename, 'r')
    for line in fin:
        if ' PhanRedefine' in line:
            match = REDEFINE_REGEX.match(line)
            if match is not None:
                original = match.group(1)
                other = match.group(2)
                yield original, other, line


def main():
    if len(sys.argv) != 2:
        print("Usage: {} pylint_results.txt".format(sys.argv[1]))
        print("  This script parses pylint analysis results and spits out a list of exclusions for duplicate class/function entries")
        sys.exit(1)
    filename = sys.argv[1]
    sys.stderr.write("Choosing files to exclude in '{}'\n".format(filename))
    excluded_files = set()
    for original, other, line in get_redefine_pairs(filename):
        if original in excluded_files:
            continue
        if other in excluded_files:
            continue
        excluded_files.add(other if compare_files(original, other) else original)

    print("    'exclude_file_list' => [")
    if len(excluded_files) > 0:
        print('        // These files were excluded because they duplicated class or method definitions')
    for excluded_filename in sorted(excluded_files):
        print('        ' + repr(excluded_filename) + ',')
    print("    ],")


if __name__ == '__main__':
    main()
