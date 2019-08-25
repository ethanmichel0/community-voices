<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

  <xsl:import href="../Component/Navbar.xslt" />
  <xsl:output method="html" indent="yes" omit-xml-declaration="yes" />

  <xsl:variable name="isManager" select="package/identity/user/role = 'manager'
    or package/identity/user/role = 'administrator'" />

  <xsl:template match="/package">

    <xsl:call-template name="navbar" />

    <div class="row pb-0" style="padding:15px;">
      <div class="col-12">
        <div id="carouselIndicators" class="carousel slide" data-ride="carousel" data-interval="7000">
          <div class="carousel-inner">
            <xsl:for-each select="domain/slideCollection/slide">
              <xsl:variable name="i" select="position()" />
              <xsl:choose>
                <xsl:when test="$i = 1">
                  <div class="carousel-item active">
                    <div class="embed-responsive embed-responsive-16by9 mb-4">
                      <iframe class="embed-responsive-item" id="slide{$i}" style="pointer-events: none;" src="https://environmentaldashboard.org/community-voices/slides/{id}"></iframe>
                    </div>
                  </div>
                </xsl:when>
                <xsl:otherwise>
                  <div class="carousel-item">
                    <div class="embed-responsive embed-responsive-16by9 mb-4">
                      <iframe class="embed-responsive-item" id="slide{$i}" style="pointer-events: none;" src="https://environmentaldashboard.org/community-voices/slides/{id}"></iframe>
                    </div>
                  </div>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:for-each>
          </div>
          <a class="carousel-control-prev" href="#carouselIndicators" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
          <a class="carousel-control-next" href="#carouselIndicators" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
        </div>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; padding: 0px 15px">
        <xsl:for-each select="domain/contentCategoryCollection/contentCategory">
            <div style="display: flex; flex-direction: column; width: 130px">
                <div style="display: flex; justify-content: center; align-content: center; background-color: {color}; border-radius: 10px; height: 105px; width: 130px">
                    <img data-cc="{id}" src="/community-voices/uploads/{image/image/id}" style="cursor: pointer; margin: auto; max-width: 130px; max-height: 105px" />
                </div>

                <div style="text-align:center; font-weight:bold"><xsl:value-of select="label" /></div>
            </div>
        </xsl:for-each>
    </div>

    <div class="row mb-5" style="padding: 15px">
      <form action="/community-voices/slides" method="GET" style="width:100%;padding:15px" id="search-form">
        <h4 class="mb-2">Looking for more content?</h4>
        <div class="input-group input-group-lg">
          <input name="search" type="text" class="form-control" aria-label="Search Community Voices" placeholder="Search slides, images, or quotes" style="background: url(https://environmentaldashboard.org/community-voices/public/images/search.svg) no-repeat left 1rem center;background-size: 20px 20px;padding-left: 3rem" />
          <div class="input-group-append">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="dropdown-btn">Slides</button>
            <div class="dropdown-menu" id="searchables">
              <a class="dropdown-item" data-action="/community-voices/slides" href="#">Slides</a>
              <a class="dropdown-item" data-action="/community-voices/images" href="#">Images</a>
              <a class="dropdown-item" data-action="/community-voices/quotes" href="#">Quotes</a>
              <a class="dropdown-item" data-action="/community-voices/articles" href="#">Articles</a>
            </div>
          </div>
          <button type="submit" class="btn btn-outline-primary form-control" style="max-width:15%">Search</button>
        </div>
      </form>
    </div>



  </xsl:template>

</xsl:stylesheet>
