// Opens the SIMDA master SVG, gives every logo version (group AB-*) its own artboard, saves as .ai
app.userInteractionLevel = UserInteractionLevel.DONTDISPLAYALERTS;
var src = new File("C:/xampp/htdocs/SIMDA/brand/_src/simda-logo-master.svg");
var dst = new File("C:/xampp/htdocs/SIMDA/brand/SIMDA-Logo.ai");
var doc = app.open(src);
var names = { "AB-primary": "Primary - full colour", "AB-reverse": "Reverse - on green", "AB-flat": "Flat colours", "AB-mono-green": "One colour green", "AB-black": "Black", "AB-white": "White", "AB-tagline": "Logo + tagline", "AB-mark": "S mark", "AB-icon": "App icon" };
var found = [];
function walk(items) {
    for (var i = 0; i < items.length; i++) {
        var it = items[i];
        if (it.name && names[it.name]) { found.push(it); continue; }
        if (it.typename === "GroupItem") walk(it.pageItems);
    }
}
for (var l = 0; l < doc.layers.length; l++) walk(doc.layers[l].pageItems);
found.reverse();
var first = true;
for (var k = 0; k < found.length; k++) {
    var g = found[k];
    var b = g.visibleBounds; // [left, top, right, bottom]
    if (first) { doc.artboards[0].artboardRect = b; doc.artboards[0].name = names[g.name]; first = false; }
    else { var ab = doc.artboards.add(b); ab.name = names[g.name]; }
}
// drop the white helper backgrounds (only used to size the artboards)
for (var d = doc.pageItems.length - 1; d >= 0; d--) { var pi = doc.pageItems[d]; if (pi.name && pi.name.indexOf("bg-drop") === 0) pi.remove(); }
var opts = new IllustratorSaveOptions();
opts.pdfCompatible = true;
opts.embedICCProfile = false;
opts.compressed = true;
doc.saveAs(dst, opts);
var n = doc.artboards.length;
doc.close(SaveOptions.DONOTSAVECHANGES);
"saved " + n + " artboards, groups found " + found.length;
