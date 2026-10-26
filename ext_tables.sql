CREATE TABLE tx_wnraisenow_form (
    title varchar(255) DEFAULT '' NOT NULL,
    widget_id varchar(100) DEFAULT '' NOT NULL,
    widget_version varchar(30) DEFAULT 'latest' NOT NULL,
    language varchar(5) DEFAULT '' NOT NULL,
    test_mode smallint(5) unsigned DEFAULT '0' NOT NULL,
    currencies varchar(255) DEFAULT '' NOT NULL,
    default_currency varchar(10) DEFAULT '' NOT NULL,
    payment_types varchar(255) DEFAULT '' NOT NULL,
    default_payment_type varchar(20) DEFAULT '' NOT NULL,
    recurring_intervals varchar(255) DEFAULT '' NOT NULL,
    default_recurring_interval varchar(20) DEFAULT '' NOT NULL,
    highlight_recurring smallint(5) unsigned DEFAULT '0' NOT NULL,
    amounts_onetime varchar(255) DEFAULT '' NOT NULL,
    amounts_recurring varchar(255) DEFAULT '' NOT NULL,
    default_amount double(11,2) DEFAULT '0.00' NOT NULL,
    allow_custom_amount varchar(1) DEFAULT '' NOT NULL,
    minimum_custom_amount double(11,2) DEFAULT '0.00' NOT NULL,
    maximum_custom_amount double(11,2) DEFAULT '0.00' NOT NULL,
    payment_methods varchar(255) DEFAULT '' NOT NULL,
    default_payment_method varchar(20) DEFAULT '' NOT NULL,
    purposes int(11) unsigned DEFAULT '0' NOT NULL,
    default_purpose varchar(50) DEFAULT '' NOT NULL,
    result_page varchar(1024) DEFAULT '' NOT NULL,
    additional_config text
);

CREATE TABLE tx_wnraisenow_purpose (
    form int(11) unsigned DEFAULT '0' NOT NULL,
    identifier varchar(50) DEFAULT '' NOT NULL,
    label varchar(255) DEFAULT '' NOT NULL,
    campaign_id varchar(100) DEFAULT '' NOT NULL,
    campaign_subid varchar(100) DEFAULT '' NOT NULL,

    KEY form (form)
);

CREATE TABLE tt_content (
    tx_wnraisenow_form varchar(255) DEFAULT '' NOT NULL
);
