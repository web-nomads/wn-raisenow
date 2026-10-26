/**
 * Loads the RaiseNow Tamaro widget for the first donation form on the page.
 * Tamaro keeps a single instance in window.rnw.tamaro, so further forms stay empty.
 */
(() => {
  const containers = document.querySelectorAll('[data-wn-raisenow]');
  if (containers.length === 0) {
    return;
  }
  if (containers.length > 1) {
    console.warn('wn_raisenow: Tamaro renders one widget per page, only the first donation form is shown');
  }

  const container = containers[0];
  let settings;
  try {
    settings = JSON.parse(container.dataset.wnRaisenow);
  } catch (error) {
    console.error('wn_raisenow: invalid widget settings', error);
    return;
  }

  const buildResultUrl = (widget) => {
    const url = new URL(settings.resultUrl, window.location.href);
    const set = (name, value) => {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(name, value);
      }
    };
    // RaiseNow Hub
    if (widget.epmsPaymentInfo) {
      set('epms_payment_uuid', widget.epmsPaymentInfo.uuid);
      set('epms_payment_status', widget.epmsPaymentInfo.last_status);
    }
    if (widget.epmsSubscriptionInfo) {
      set('epms_subscription_uuid', widget.epmsSubscriptionInfo.uuid);
      set('epms_subscription_status', widget.epmsSubscriptionInfo.status);
    }
    // RaiseNow Manager (legacy)
    if (widget.transactionInfo && !widget.subscriptionInfo) {
      set('epp_payment_id', widget.transactionInfo.epp_transaction_id);
      set('epp_payment_status', widget.transactionInfo.epayment_status);
    }
    if (widget.subscriptionInfo) {
      set('epp_subscription_token', widget.subscriptionInfo.subscription_token);
      set('epp_subscription_status', widget.subscriptionInfo.status);
    }
    return url.href;
  };

  const run = () => {
    const tamaro = window.rnw && window.rnw.tamaro;
    if (!tamaro) {
      console.error('wn_raisenow: Tamaro did not load from ' + settings.widgetUrl);
      return;
    }
    const config = Object.assign({}, settings.config);
    if (settings.resultUrl) {
      config.redirectToCustomResultPage = (widget) => {
        window.location.replace(buildResultUrl(widget));
      };
    }
    tamaro.runWidget('#' + container.id, config);
  };

  const script = document.createElement('script');
  script.src = settings.widgetUrl;
  script.async = true;
  script.addEventListener('load', run);
  script.addEventListener('error', () => console.error('wn_raisenow: could not load ' + settings.widgetUrl));
  document.head.appendChild(script);
})();
