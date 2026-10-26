<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

// sources the Tamaro widget and its payment providers need, applies to sites inheriting the default policy
return Map::fromEntries([
    Scope::frontend(),
    new MutationCollection(
        new Mutation(
            MutationMode::Extend,
            Directive::ScriptSrc,
            new UriValue('https://tamaro.raisenow.com'),
            new UriValue('https://cdn.jsdelivr.net'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::StyleSrc,
            new UriValue('https://tamaro.raisenow.com'),
            new UriValue('https://assets.raisenow.io'),
            new UriValue('https://cdn.jsdelivr.net'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::FontSrc,
            new UriValue('https://assets.raisenow.io'),
            new UriValue('https://cdn.jsdelivr.net'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            new UriValue('https://*.raisenow.com'),
            new UriValue('https://*.raisenow.io'),
            new UriValue('https://cdn.jsdelivr.net'),
            new UriValue('https://data.jsdelivr.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            new UriValue('https://*.raisenow.com'),
            new UriValue('https://*.raisenow.io'),
            new UriValue('https://cdn.jsdelivr.net'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::FrameSrc,
            new UriValue('https://*.raisenow.com'),
            new UriValue('https://*.raisenow.io'),
            new UriValue('https://pay.datatrans.com'),
            new UriValue('https://pay.sandbox.datatrans.com'),
            new UriValue('https://www.paypal.com'),
            new UriValue('https://www.sandbox.paypal.com'),
            new UriValue('https://js.stripe.com'),
        ),
    ),
]);
