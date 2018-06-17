<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0">

  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

  <xsl:variable name="isManager" select="package/identity/user/role = 'manager'
    or package/identity/user/role = 'administrator'"/>

    <xsl:template match="/package">

      <xsl:choose><xsl:when test="$isManager or domain/image/status = 'approved'">

        <div class="card mb-3">
          <img class="card-img-top">
            <xsl:attribute name="src">./uploads/<xsl:value-of select='domain/image/id' /></xsl:attribute>
            <xsl:attribute name="alt"><xsl:value-of select='domain/image/title' /></xsl:attribute>
          </img>
          <div class="card-body">
            <h3 class="card-title"><xsl:value-of select='domain/image/title' /></h3>
            <h6 class="card-subtitle mb-2 text-muted">
              <xsl:value-of select='domain/image/photographer' />
              <xsl:if test="not(domain/image/organization = '')">, <xsl:value-of select='domain/image/organization' /></xsl:if>
            </h6>
            <p class="card-text"><xsl:value-of select='domain/image/description' /></p>
            <p class="card-text"><small class="text-muted">Created <xsl:value-of select='domain/image/dateCreated' /></small></p>
          </div>
          <xsl:if test="$isManager">
            <div class="card-footer text-muted">
              <p class='mt-0 mb-0'>
                Uploader:
                <xsl:value-of select='domain/image/addedBy/user/firstName' />
                <xsl:text> </xsl:text>
                <xsl:value-of select='domain/image/addedBy/user/lastName' />
              </p>
              <p class='mt-0 mb-0'>
                Status:
                <xsl:value-of select='domain/image/status' />
              </p>
              <p class='mt-0 mb-0'>Tags: TODO</p>
              <p class='mt-0 mb-0'>
                <a>
                    <xsl:attribute name="href">./images/<xsl:value-of select='domain/image/id'/>/edit</xsl:attribute>
                    Edit
                </a>
              </p>
            </div>
          </xsl:if>
        </div>

        <div class="middle">

          <img style="width:100%;">
              
          </img>
          <p></p>

        </div>

      </xsl:when>

      <xsl:otherwise>
        Unauthorized Content
      </xsl:otherwise>

    </xsl:choose>

  </xsl:template>

</xsl:stylesheet>
