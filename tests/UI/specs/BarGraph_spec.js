/*!
 * Piwik - free/libre analytics platform
 *
 * Bar graph screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("BarGraph", function () {
    var tokenAuth = "c4ca4238a0b923820dcc509a6f75849b", // md5('superUserLogin' . md5('superUserPass'))
        url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1&"
            + "token_auth=" + tokenAuth;

    before(function () {
        // use real auth + token auth to test that auth works when widgetizing reports in an iframe
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    it("should load correctly", async function () {
        await page.goto(url);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('load');
    });

    it("should display the metric picker on hover of metric picker icon", async function () {
        await page.hover('.jqplot-seriespicker');
        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
    });

    it("should display multiple metrics when another metric picked", async function () {
        await page.waitForSelector('.jqplot-seriespicker-popover input');
        var element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):first + label');
        await element.click();
        await page.waitForNetworkIdle();
        await page.waitFor(500);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('other_metric');
    });
});