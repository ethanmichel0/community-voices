<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.0">

  <xsl:output method="html" indent="yes" omit-xml-declaration="yes" />

  <xsl:template match="/form">
    <div class="row" style="padding:15px;">
      <div class="col-12">
        <xsl:if test="@failure">
          <p>Incorrect user/pass combination.</p>
        </xsl:if>

        <form class="form-signin mb-5 pb-5" action='./login/authenticate' method='post'>
          <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>

          <label for="email" class="sr-only">Email address</label>

          <xsl:choose>
            <xsl:when test="@email-value">
              <input type='email' name='email' id="email" value='{@email-value}' class="form-control" placeholder="Email address" required="" autofocus="" />
            </xsl:when>

            <xsl:otherwise>
              <input type='email' name='email' id="email" class="form-control" placeholder="Email address" required="" autofocus=""/>
            </xsl:otherwise>
          </xsl:choose>

          <label for="password" class="sr-only">Password</label>

          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required="" />

          <div class="checkbox mb-3">
            <label>
              <xsl:choose>
                <xsl:when test="@remember-value &gt; 0">
                    <input type='checkbox' name='remember' checked='{@remember-value}'/> Remember me
                </xsl:when>

                <xsl:otherwise>
                    <input type='checkbox' name='remember' /> Remember me
                </xsl:otherwise>
              </xsl:choose>
            </label>
          </div>

          <button class="btn btn-lg btn-primary btn-block mb-5" type="submit">Sign in</button>
        </form>

      </div>
    </div>
  </xsl:template>

</xsl:stylesheet>
