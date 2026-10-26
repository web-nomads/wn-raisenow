# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-26

### Added
- **Donation form records.** A form holds the Tamaro widget ID and everything an
  editor may want to change per form: language (automatic from the page or
  fixed), forced test mode, currencies, one-off and recurring donations with
  their intervals, amounts for each payment type, preselected and custom
  amounts, payment methods in display order, and a thank-you page. Every field
  left empty keeps what RaiseNow configured for the widget.
- **Donation purposes** as translatable child records, each with a key, a label
  and an optional campaign ID and sub-ID passed on to RaiseNow.
- **Content element "RaiseNow donation form"** that renders the selected form as
  a Tamaro widget. The script is loaded only on pages with a donation form, and
  the configuration travels as a data attribute instead of inline JavaScript.
- **Thank-you page redirect** through Tamaro's `redirectToCustomResultPage`,
  with payment or subscription reference and status appended for RaiseNow Hub
  and for widgets still on the RaiseNow Manager.
- **Additional configuration as JSON** for any Tamaro option the form does not
  offer as a field.
- Site set for TYPO3 13 and 14, static template for TYPO3 12.
- Unit and functional tests, run against TYPO3 12.4, 13.4 and 14.3.

[1.0.0]: https://github.com/web-nomads/wn-raisenow/releases/tag/v1.0.0
