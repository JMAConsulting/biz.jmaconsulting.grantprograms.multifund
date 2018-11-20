# Grant Programs Multifund

This extension allows a backend user to add multiple fund sources for a grant. In other words, it lets you pay for 
a grant using monies from several different accounts, rather than having a single fund/account that pays for it all.

biz.jmaconsulting.grantprograms.multifund is a 'sub-extension', since it extends the functionality of the Grant Programs extension.

## Requirements

* CiviCRM 5.7+
* [Grant Programs](https://github.com/JMAConsulting/biz.jmaconsulting.grantprograms)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv). We
recommend taking a .zip file based on the latest tag, rather than one based
on the master branch which is not intended to be production ready all of the time.

```bash
cd <extension-dir>
cv dl biz.jmaconsulting.grantprograms.multifund@https://github.com/JMAConsulting/biz.jmaconsulting.grantprograms.multifund/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv). We recommend using the latest
tag release, as the master branch is not intended to be production ready all of the time.

```bash
git clone https://github.com/JMAConsulting/biz.jmaconsulting.grantprograms.multifund.git
cv en multifund
```

## Usage

This [screencast](https://gfycat.com/UnhealthyBadHawk) shows an empty accounting batch which later ends up containing multiple fund transactions of a single grant after it has been paid.
