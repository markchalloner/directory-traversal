# Directory traversal tester

Takes a series of paths on the STDIN and tests the against a filtering regular expression to ensure safety from directory traversal attacks.

## Installation

```
git clone http://github.com/markchalloner/directory-traversal
```

## Usage

```
directory-traversal/run.php [regular-expression]

Options:
    regular-expression Optional. A PHP regular expression to test as a filter. If not specified the filter will be disabled and all requested directory traversal will happen!
```

### Basic example

```
echo -e "../hacked\n../../hacked" | directory-traversal/run.php '#\.\.[\\\/]#'
```

### [DotDotPwn] example

Assuming [DotDotPwn] has been installed alongside directory-traversal with:

```
git clone https://github.com/wireghoul/dotdotpwn.git
```

Run

```
(cd dotdotpwn && ./dotdotpwn.pl -f 'hacked' -d 1 -m 'stdout' | ../directory-traversal/run.php '#\.\.[\\\/]#')
```

## Reset test directories

To avoid false positives on subsequent runs reset your test directory hierachy (root/**):

```
(cd directory-traversal && git clean -f -X -- root)
```

[DotDotPwn]: https://github.com/wireghoul/dotdotpwn
