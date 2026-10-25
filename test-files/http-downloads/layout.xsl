<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" encoding="UTF-8" />
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Archive example</title>
            </head>
            <body data-layout="outer-layout">
                <h1>Archive listing</h1>
                <xsl:for-each select="//archive/file">
                    <p>
                        <xsl:value-of select="@name" />
                    </p>
                </xsl:for-each>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
