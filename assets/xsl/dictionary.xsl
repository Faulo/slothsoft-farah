<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sfd="http://schema.slothsoft.net/farah/dictionary" xmlns:func="http://exslt.org/functions"
    xmlns:php="http://php.net/xsl" extension-element-prefixes="sfd func php">

    <func:function name="sfd:lookup-text">
        <xsl:param name="key" select="." />
        <xsl:param name="dictionary" select="''" />
        <xsl:param name="language" select="''" />

        <func:result select="php:functionString('Slothsoft\Farah\Dictionary::xsltLookupText', string($key), string($dictionary), string($language))" />
    </func:function>

    <func:function name="sfd:sanitize-key">
        <xsl:param name="key" select="." />

        <func:result select="php:functionString('Slothsoft\Farah\Dictionary::xsltSanitizeKey', string($key))" />
    </func:function>

    <func:function name="sfd:current-language">
        <func:result select="php:functionString('Slothsoft\Farah\Dictionary::xsltCurrentLanguage')" />
    </func:function>
</xsl:stylesheet>