<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:lio="http://schema.slothsoft.net/xslt"
	xmlns:date="http://exslt.org/dates-and-times"
	xmlns:func="http://exslt.org/functions"
	xmlns:php="http://php.net/xsl"
	extension-element-prefixes="lio date func php">
	
	<func:function name="lio:date_format">
		<xsl:param name="datetime"/>
		<xsl:param name="format" select="'Y-m-d H:i:s'"/>
		
		<func:result select="php:function(
			'date',
			$format,
			php:function('strtotime', $datetime)
		)"/>
	</func:function>
	
</xsl:stylesheet>