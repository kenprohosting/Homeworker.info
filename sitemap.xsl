<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>Sitemap - homeworker.info</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          h1 { color: #333; }
          table { border-collapse: collapse; width: 100%; }
          th, td { padding: 8px 12px; border: 1px solid #ccc; }
          th { background: #f5f5f5; text-align: left; }
          tr:nth-child(even) { background: #fafafa; }
          a { color: #0066cc; text-decoration: none; }
          a:hover { text-decoration: underline; }
        </style>
      </head>
      <body>
        <h1>Sitemap for homeworker.info</h1>
        <table>
          <tr>
            <th>URL</th>
            <th>Last Modified</th>
            <th>Priority</th>
          </tr>
          <xsl:for-each select="urlset/url">
            <tr>
              <td><a href="{loc}"><xsl:value-of select="loc"/></a></td>
              <td><xsl:value-of select="lastmod"/></td>
              <td><xsl:value-of select="priority"/></td>
            </tr>
          </xsl:for-each>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
