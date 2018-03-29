<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/data">
		<xsl:variable name="cols" select="*[@data-cms-name = 'ipLog']/col"/>
		<xsl:variable name="logs" select="*[@data-cms-name = 'ipLog']/log"/>
		<xsl:variable name="pages" select="*[@data-cms-name = 'ipLog']/page"/>
		<xsl:variable name="groups" select="*[@data-cms-name = 'ipLog']/groupList"/>
		<xsl:variable name="languages" select="*[@data-cms-name = 'ipLog']/language"/>
		<xsl:variable name="langRegistry" select="*[@data-cms-name = 'language-registry']/registry/language"/>
		<xsl:variable name="regionRegistry" select="*[@data-cms-name = 'language-registry']/registry/region"/>
		<xsl:variable name="encodingList" select="*[@data-cms-name = 'ipLog']/encoding"/>
		<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
		<xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyz'"/>
		<html>
			<head>
				<title>IP Log</title>
				<style type="text/css"><![CDATA[
/* http://colorschemedesigner.com/#4e527hWs0g0g0 */
body {
	text-align: center;
	margin: 0px;
}
header {
	/*
	position: fixed;
	width: 100%;
	top: 0px;
	*/
	padding: 0 2px 0 2px;
}
nav {
	display: table;
	width: 100%;
	font-size: smaller;
	border-top: 2px solid white;
}
nav > * {
	display: table-cell;
	vertical-align: top;
	padding: 1px 0;
}
nav > *:nth-child(odd) {
	background-color: #A2D578;
}
nav > *:nth-child(even) {
	background-color: #9CD56D;
}
nav > *.active {
	color: white;
	background-color: #68A436;
	text-decoration: none;
	font-weight: bold;
}
nav > *:hover {
	background-color: #8AC757;
}
table {
	border-spacing: 2px;
	/*
	margin-top: 4em;
	*/
}
tr:nth-child(odd) {
	background-color: #E7E783;
}
tr:nth-child(even) {
	background-color: #E7CE83;
}
input {
	width: 100%;
	border: none;
	padding: 0px;
	background-color: transparent;
}
button {
	display: none;
}
*[data-col] {
	overflow: hidden;
}
tr[id] > td:nth-child(3) > input, tr[id] > td:nth-child(4) > input {
	text-align: right;
	font-family: monospace;
	font-size: 0.8em;
}
*[data-col="RESPONSE_STATUS"], *[data-col="REQUEST_METHOD"], *[data-col="RESPONSE_TIME"], *[data-col="RESPONSE_MEMORY"], *[data-col="RESPONSE_LANGUAGE"], *[data-col="RESPONSE_ENCODING"] {
	min-width: 32px;
	max-width: 32px;
}
*[data-col="RESPONSE_TYPE"], *[data-col="HTTP_LAST_EVENT_ID"] {
	min-width: 64px;
	max-width: 64px;
}
*[data-col="HTTP_HOST"], *[data-col="REQUEST_TIME_DATE"], *[data-col="REMOTE_ADDR"] {
	min-width: 100px;
	max-width: 100px;
}
*[data-col="HTTP_ACCEPT_LANGUAGE"], *[data-col="HTTP_ACCEPT_ENCODING"] {
	min-width: 128px;
	max-width: 128px;
}
*[data-col="HTTP_ACCEPT"], *[data-col="HTTP_FROM"] {
	min-width: 256px;
	max-width: 256px;
}
*[data-col="REQUEST_URI"], *[data-col="HTTP_USER_AGENT"], *[data-col="HTTP_REFERER"], *[data-col="RESPONSE_INPUT"], *[data-col="RESPONSE_OUTPUT"] {
	min-width: 512px;
	max-width: 512px;
}

			]]></style>
			</head>
			<body>
				<header>
					<!--
					<nav class="language">
						<xsl:for-each select="$languages">
							<xsl:variable name="langNode" select="$langRegistry[subtag = current()/@lang]"/>
							<xsl:variable name="regionNode" select="$regionRegistry[subtag = current()/@region]"/>
							<a href="{@uri}" class="{@active}">
								<xsl:if test="$langNode">
									<xsl:attribute name="title">
										<xsl:value-of select="$langNode/description"/>
										<xsl:text> (</xsl:text>
										<xsl:value-of select="$regionNode/description"/>
										<xsl:text>)</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="substring-before(@code, '-')"/>
								<br/>
								<xsl:value-of select="substring-after(@code, '-')"/>
							</a>
						</xsl:for-each>
					</nav>
					<nav class="encoding">
						<xsl:for-each select="$encodingList">
							<a href="{@uri}" class="{@active}">
								<xsl:value-of select="@code"/>
							</a>
						</xsl:for-each>
					</nav>
					<nav class="page">
						<xsl:for-each select="$pages">
							<xsl:if test="position() &lt; 100 or position() = last()">
								<a href="{@uri}" class="{@active}"><xsl:value-of select="position()"/></a>
							</xsl:if>
						</xsl:for-each>
					</nav>
					-->
					<xsl:for-each select="$groups">
						<nav>
							<xsl:for-each select="group">
								<a href="{@uri}" class="{@active}"><xsl:value-of select="."/></a>
							</xsl:for-each>
						</nav>
					</xsl:for-each>
				</header>
				<form action="{$groups[last()]/group[1]/@uri}" method="POST">
					<button type="submit"/>
					<table>
						<thead>
							<tr>
								<td/>
								<xsl:for-each select="$cols">
									<th data-col="{.}" title="{.}"><xsl:value-of select="."/></th>
								</xsl:for-each>
							</tr>
							<tr>
								<td/>
								<xsl:for-each select="$cols">
									<th data-col="{.}">
										<xsl:if test="@searchable">
											<input name="{@form-key}" value="{@form-val}"/>
										</xsl:if>
									</th>
								</xsl:for-each>
							</tr>
						</thead>
						<tbody>
							<xsl:for-each select="$logs">
								<xsl:variable name="log" select="."/>
								<tr><!--  id="row-{@id}" -->
									<td>
										<a href="http://{$log/@*[name() = 'HTTP_HOST']}{$log/@*[name() = 'REQUEST_URI']}">🔗</a>
										<xsl:text>&#160;</xsl:text>
										<a href="http://www.utrace.de/?query={$log/@*[name() = 'REMOTE_ADDR']}">🌏</a>
									</td>
									<xsl:for-each select="$cols">
										<td>
											<xsl:variable name="val" select="string($log/@*[name() = current()])"/>
											<input value="{$val}"/>
										</td>
									</xsl:for-each>
								</tr>
								<xsl:text>
</xsl:text>
							</xsl:for-each>
						</tbody>
					</table>
				</form>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>