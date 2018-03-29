<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:variable name="cdata-start">
		<xsl:text disable-output-escaping="yes">&lt;![CDATA[</xsl:text>
	</xsl:variable>
	<xsl:variable name="cdata-end">
		<xsl:text disable-output-escaping="yes">]]&gt;</xsl:text>
	</xsl:variable>
	
	<xsl:template match="/data/data">
		<html>
			<head>
				<title>Language Management</title>
				<style type="text/css"><![CDATA[

			]]></style>
			</head>
			<body>
				<h1>Language Management</h1>
				<header>
					<nav>
						<h2>Module Selection</h2>
						<ul>
							<xsl:for-each select="module">
								<li><a href="./?module={@name}"><xsl:value-of select="@name"/></a></li>
							</xsl:for-each>
						</ul>
					</nav>
				</header>
				<main>
					<xsl:for-each select="module[@current]">
						<xsl:variable name="fileList" select="html:html"/>
						<form method="POST" action=".">
							<fieldset>
								<legend><h2><xsl:value-of select="@name"/></h2></legend>
								<table border="1">
									<thead>
										<tr>
											<th>key</th>
											<xsl:for-each select="$fileList">
												<th><xsl:value-of select="@xml:lang"/></th>
											</xsl:for-each>
										</tr>
									</thead>
									<tbody>
										<xsl:for-each select="$fileList/html:p">
											<xsl:variable name="key" select="@key"/>
											<tr>
												<td><xsl:value-of select="$key"/></td>
												<xsl:for-each select="$fileList">
													<td>
														<textarea cols="40" rows="6">
															<xsl:for-each select="html:p[@key = $key]">
																<xsl:copy-of select="$cdata-start"/>
																<xsl:copy-of select="node()"/>
																<xsl:copy-of select="$cdata-end"/>
															</xsl:for-each>
														</textarea>
													</td>
												</xsl:for-each>
											</tr>
										</xsl:for-each>
									</tbody>
								</table>
							</fieldset>
						</form>
					</xsl:for-each>
				</main>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>