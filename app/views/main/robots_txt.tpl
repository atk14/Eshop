{assign ATK14_GLOBAL Atk14Global::GetInstance()}
User-agent: *
Disallow: /recovery/*
Disallow: /obnova/*
{* According to https://developers.google.com/search/docs/crawling-indexing/qualify-outbound-links#nofollow *}
{**
 * Crawler does not visit urls that start with path in Disallow. Wildcard * is not needed.
 *
 * @see https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt#url-matching-based-on-path-values
 *}
{foreach $ATK14_GLOBAL->getSupportedLangs() as $lang}
Disallow: {link_to controller="baskets" action="index" lang=$lang}
Disallow: {link_to controller="favourite_products" action="index" lang=$lang}
Disallow: {link_to controller="watched_products" action="index" lang=$lang}
Disallow: {link_to controller="cookie_consents" action="index" lang=$lang}
{/foreach}
Disallow: /*offset=*
Disallow: /*order=*
Disallow: /*count=*
Sitemap: {link_to controller="sitemaps" _with_hostname=true}
