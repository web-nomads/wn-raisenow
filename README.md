# RaiseNow donation forms

Puts [RaiseNow](https://raisenow.com) donation forms on TYPO3 pages. Editors
configure a form in the backend — amounts, currencies, payment methods,
purposes, thank-you page — and place it on any page as a content element. The
form itself is RaiseNow's Tamaro widget, so payments, receipts and reporting run
through RaiseNow as before.

## Requirements

| TYPO3 | PHP |
|---|---|
| 12.4 LTS | 8.1 – 8.5 |
| 13.4 LTS | 8.2 – 8.5 |
| 14.3 | 8.2 – 8.5 |

Composer-based and classic (non-Composer) installations are both supported.

## What you need from RaiseNow

A **Tamaro widget ID**. RaiseNow sends it with the onboarding e-mail, and it is
listed in RaiseNow Hub under *Tamaro donation widgets* as the *configuration
name*. It is the part between `tamaro.raisenow.com/` and `/latest/widget.js` in
the embed code RaiseNow provides:

```html
<script src="https://tamaro.raisenow.com/my-organisation-de/latest/widget.js"></script>
```

The embeddable forms of RaiseNow Hub's Free plan (iframe) and Paylinks are not
Tamaro widgets and are not covered by this extension.

## Installation

**With Composer:**

```bash
composer require web-nomads/wn-raisenow
vendor/bin/typo3 extension:setup
```

**Without Composer (classic mode):** install *wn_raisenow* from the TYPO3
Extension Repository in the Extension Manager, or upload the archive there.
Activating it creates the database tables.

On sites using `typo3/cms-fluid-styled-content` — through its site set or its
static template — nothing else needs to be included: the extension adds its
TypoScript right after the content rendering of fluid_styled_content, the way
felogin and form do.

Sites rendering content with another library need to include the TypoScript
themselves, and that library has to define `lib.contentElement` with a
`Default` layout:

- **TYPO3 13 and 14:** add the site set *RaiseNow donation forms*
  (`web-nomads/raisenow`) to the site.
- **TYPO3 12**, or sites without site sets: include the static template
  *RaiseNow donation forms* in the TypoScript record.

## Usage

1. Create a folder in the page tree and add a **Donation form** record there
   (list module). Enter the widget ID; everything else is optional.
2. Add the content element **RaiseNow donation form** to a page and select the
   record.

### Options of a donation form

Every option left empty keeps what RaiseNow configured for the widget. Filling
in a field overrides that value for this form only.

| Tab | Option | Tamaro option |
|---|---|---|
| Widget | Widget ID, widget version (`latest` by default) | script URL |
| | Language: automatic (page language) or fixed | `language` |
| | Force test mode | `testMode` |
| Amounts | Currencies, preselected currency | `currencies`, `defaultCurrency` |
| | One-off / recurring, preselected payment type | `paymentTypes`, `defaultPaymentType` |
| | Intervals, preselected interval, highlight recurring | `recurringIntervals`, `defaultRecurringInterval`, `highlightRecurring` |
| | Amounts for one-off and for recurring donations | `amounts` |
| | Preselected amount, custom amount allowed, minimum, maximum | `defaultAmount`, `allowCustomAmount`, `minimumCustomAmount`, `maximumCustomAmount` |
| Payment methods | Payment methods in the order selected, preselected method | `paymentMethods`, `defaultPaymentMethod` |
| Purposes | Purposes with key, label and campaign ID / sub-ID; preselected purpose | `purposes`, `translations`, `purposeDetails`, `defaultPurpose` |
| Advanced | Thank-you page | `redirectToCustomResultPage` |
| | Additional configuration as JSON | any option |

**Purposes** are translated like any other record: translate the donation form
and its purposes, and the widget shows the labels of the page language. The
campaign ID and sub-ID are stored with the donation in RaiseNow
(`stored_campaign_id`, `stored_campaign_subid`).

**Thank-you page:** after the payment the donor is redirected there instead of
seeing the result inside the widget. The payment reference and status are
appended to the URL — `epms_payment_uuid` and `epms_payment_status` for RaiseNow
Hub, `epms_subscription_uuid` and `epms_subscription_status` for recurring
donations, and the `epp_*` equivalents for widgets still running on the
RaiseNow Manager.

**Additional configuration** takes a JSON object with any option from the
[Tamaro documentation](https://docs.raisenow.com/elements/tamaro). Its top-level
keys replace whatever the fields above produced, for example:

```json
{"showFooter": false, "salutations": ["ms", "mr"]}
```

Invalid JSON is ignored rather than breaking the form.

### Things to know

- **One donation form per page.** Tamaro keeps a single widget instance per
  page, so only the first donation form on a page is shown.
- **Test mode** is set by RaiseNow: a new widget starts in test mode and RaiseNow
  switches it to production once it is integrated. *Force test mode* is meant
  for a test page; it cannot switch a widget to production. Adding
  `#widgetdebug` to the page URL opens Tamaro's debug panel.
- **Payment methods** only appear if they are activated for your RaiseNow
  account, whatever is selected here.
- **Content Security Policy:** the extension extends the frontend policy with
  what the widget needs — scripts from `tamaro.raisenow.com` and
  `cdn.jsdelivr.net`, styles and fonts from `assets.raisenow.io`, requests to
  RaiseNow, and frames of RaiseNow, Datatrans, PayPal and Stripe. This applies
  to every site whose `csp.yaml` keeps `inheritDefault: true`; a site that
  defines its policy from scratch has to add these sources itself.

## Styling

The widget sits in a container `<div class="wn-raisenow">`. A default
stylesheet gives that container a white background and 30 pixels of padding,
so the form does not run up to the edge of the white area Tamaro draws. It is
only added to pages that show a donation form.

**Adjust the default** in your own CSS — its rule has zero specificity
(`:where()`), so any selector of yours wins without `!important`:

```css
.wn-raisenow {
    --wn-raisenow-padding: 40px;
    --wn-raisenow-background: transparent;
}
```

**Replace the default** with your own file, or switch it off:

```typoscript
# own stylesheet instead of the default
tt_content.wnraisenow_form.settings.stylesheet = EXT:my_sitepackage/Resources/Public/Css/donation-form.css

# SCSS, compiled by ws_scss
tt_content.wnraisenow_form.settings.stylesheet = EXT:my_sitepackage/Resources/Private/Scss/donation-form.scss

# no stylesheet at all
tt_content.wnraisenow_form.settings.stylesheet =
```

SCSS needs the extension [ws_scss](https://extensions.typo3.org/extension/ws_scss).
Without it a `.scss` file is skipped and a warning is logged, rather than a
stylesheet the browser cannot read being linked. Options of ws_scss such as
variables set under `plugin.tx_wsscss.variables` apply as usual.

Tamaro's own elements carry classes like `.tamaro-widget`; styling them from
the outside works, but RaiseNow may change that markup with any widget update.
For colours and fonts, prefer the styling options of the widget configuration
(see [Tamaro styling](https://docs.raisenow.com/elements/tamaro)).

## Templates

The template can be replaced via TypoScript:

```typoscript
tt_content.wnraisenow_form.templateRootPaths.1740 = EXT:my_sitepackage/Resources/Private/Templates/RaiseNow/
```

It receives `{raisenow.form}` (the record), `{raisenow.config}` (the Tamaro
configuration as an array) and `{raisenow.settings}` (the JSON the JavaScript
reads from `data-wn-raisenow`). `{raisenow}` is empty if the selected record is
hidden or has no valid widget ID.

## Development

```bash
composer install
composer ci                 # coding standards, PHPStan, unit and functional tests
composer test:functional    # SQLite by default
```

The GitHub workflow runs the suite against TYPO3 12.4, 13.4 and 14.3 on every
supported PHP version.

## Licence

GPL-2.0-or-later. This extension is not affiliated with RaiseNow.
