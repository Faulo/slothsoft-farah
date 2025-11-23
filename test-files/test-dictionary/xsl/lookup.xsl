<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://schema.slothsoft.net/farah/dictionary"
    xmlns:sfd="http://schema.slothsoft.net/farah/dictionary">

    <xsl:include href="farah://slothsoft@farah/xsl/dictionary" />

    <xsl:template match="sfd:dictionary">
        <dictionary>
            <xsl:copy-of select="@*" />
            <xsl:apply-templates select="*" />
        </dictionary>
    </xsl:template>

    <xsl:template match="sfd:entry">
        <entry>
            <xsl:copy-of select="@*" />
            <lookup key="{@xml:id}" />
        </entry>
    </xsl:template>

    <xsl:template match="sfd:fragment">
        <fragment>
            <xsl:copy-of select="@*" />
            <lookup key="{@xml:id}" />
        </fragment>
    </xsl:template>

    <xsl:template match="sfd:text">
        <text>
            <xsl:copy-of select="@*" />
            <lookup key="{@xml:id}" />
        </text>
    </xsl:template>
</xsl:stylesheet>