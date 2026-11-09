app.userInteractionLevel = UserInteractionLevel.DONTDISPLAYALERTS;
var doc = app.open(new File("C:/xampp/htdocs/SIMDA/brand/SIMDA-Logo.ai"));
var out = [];
for (var i = 0; i < doc.artboards.length; i++) out.push(doc.artboards[i].name);
// PDF (all artboards)
var pdf = new PDFSaveOptions(); pdf.preserveEditability = true; pdf.compatibility = PDFCompatibility.ACROBAT7;
doc.saveAs(new File("C:/xampp/htdocs/SIMDA/brand/SIMDA-Logo.pdf"), pdf);
// EPS
var eps = new EPSSaveOptions(); eps.saveMultipleArtboards = false; eps.embedAllFonts = true;
doc.saveAs(new File("C:/xampp/htdocs/SIMDA/brand/SIMDA-Logo.eps"), eps);
// PNG previews per artboard
for (var j = 0; j < doc.artboards.length; j++) {
    doc.artboards.setActiveArtboardIndex(j);
    var o = new ExportOptionsPNG24(); o.artBoardClipping = true; o.transparency = true; o.horizontalScale = 50; o.verticalScale = 50;
    doc.exportFile(new File("C:/xampp/htdocs/SIMDA/brand/_src/ai-check/ab" + (j + 1) + ".png"), ExportType.PNG24, o);
}
doc.close(SaveOptions.DONOTSAVECHANGES);
out.join(" | ");
