# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-10-26

### Fixed
- **The widget stayed empty on sites enforcing a Content Security Policy.**
  TYPO3's default frontend policy allows scripts from the site's own host only,
  so the browser refused to load Tamaro from tamaro.raisenow.com and the
  container remained blank. The extension now ships a policy extension for the
  frontend: scripts from tamaro.raisenow.com and cdn.jsdelivr.net, styles and
  fonts from assets.raisenow.io, requests to RaiseNow, and frames of RaiseNow,
  Datatrans, PayPal and Stripe. Verified in the browser against a real widget
  without any remaining violation.

## [1.0.1] - 2026-10-26

### Changed
- **The content element works without including any TypoScript.** On a site
  using fluid_styled_content, placing a donation form without adding the site
  set or static template ended in "has no rendering definition". The extension
  now registers its rendering as default content rendering, so TYPO3 appends it
  wherever fluid_styled_content is included — through its site set or its
  static template. The site set and static template remain for sites rendering
  content with another library.

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

[1.0.2]: https://github.com/web-nomads/wn-raisenow/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/web-nomads/wn-raisenow/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/web-nomads/wn-raisenow/releases/tag/v1.0.0
