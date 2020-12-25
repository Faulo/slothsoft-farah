<?xml version="1.0" encoding="UTF-8"?>

<!-- Copyright (c) 2010 Dave Reid <http://drupal.org/user/53892> This file is free software: you may copy, redistribute and/or 
	modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 
	2 of the License, or (at your option) any later version. This file is distributed in the hope that it will be useful, but 
	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
	General Public License for more details. You should have received a copy of the GNU General Public License along with this 
	program. If not, see <http://www.gnu.org/licenses/>. This file incorporates work covered by the following copyright and permission 
	notice: Google Sitmaps Stylesheets (GSStylesheets) Project Home: http://sourceforge.net/projects/gstoolbox Copyright (c) 
	2005 Baccou Bonneville SARL (http://www.baccoubonneville.com) License http://www.gnu.org/copyleft/lesser.html GNU/LGPL -->

<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="5.0" encoding="utf-8" indent="yes" />

	<!-- Root template -->
	<xsl:template match="/">
		<html>
			<head>
				<title>Sitemap file</title>
			</head>
			<body>
				<h1>Sitemap file</h1>
				<xsl:choose>
					<xsl:when test="//sitemap:url">
						<xsl:call-template name="sitemapTable" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="siteindexTable" />
					</xsl:otherwise>
				</xsl:choose>
			</body>
		</html>
	</xsl:template>

	<!-- siteindexTable template -->
	<xsl:template name="siteindexTable">
		<div id="information">
			<p>
				Number of sitemaps in this index:
				<xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of>
			</p>
		</div>
		<table class="tablesorter siteindex" border="1">
			<thead>
				<tr>
					<th>Sitemap URL</th>
					<th>Last modification date</th>
				</tr>
			</thead>
			<tbody>
				<xsl:apply-templates select="sitemap:sitemapindex/sitemap:sitemap">
					<xsl:sort select="sitemap:lastmod" order="descending" />
				</xsl:apply-templates>
			</tbody>
		</table>
	</xsl:template>

	<!-- sitemapTable template -->
	<xsl:template name="sitemapTable">
		<div id="information">
			<p>
				Number of URLs in this sitemap:
				<xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of>
			</p>
		</div>
		<table class="tablesorter sitemap" border="1">
			<thead>
				<tr>
					<th>URL location</th>
					<th>Last modification date</th>
					<th>Change frequency</th>
					<th>Priority</th>
				</tr>
			</thead>
			<tbody>
				<xsl:apply-templates select="sitemap:urlset/sitemap:url" />
			</tbody>
		</table>
	</xsl:template>

	<!-- sitemap:url template -->
	<xsl:template match="sitemap:url">
		<tr>
			<td>
				<xsl:variable name="sitemapURL">
					<xsl:value-of select="sitemap:loc" />
				</xsl:variable>
				<a href="{$sitemapURL}" ref="external">
					<xsl:value-of select="$sitemapURL"></xsl:value-of>
				</a>
			</td>
			<td>
				<xsl:value-of select="sitemap:lastmod" />
			</td>
			<td>
				<xsl:value-of select="sitemap:changefreq" />
			</td>
			<td>
				<xsl:value-of select="sitemap:priority" />
			</td>
		</tr>
	</xsl:template>

	<!-- sitemap:sitemap template -->
	<xsl:template match="sitemap:sitemap">
		<tr>
			<td>
				<xsl:variable name="sitemapURL">
					<xsl:value-of select="sitemap:loc" />
				</xsl:variable>
				<a href="{$sitemapURL}">
					<xsl:value-of select="$sitemapURL"></xsl:value-of>
				</a>
			</td>
			<td>
				<xsl:value-of select="sitemap:lastmod" />
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>
