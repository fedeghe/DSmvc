var cdsMvc = function(scale) {
    scale = scale || 1;
    var thick = 8;     // spessore ortogonale al segmento (molto piu spesso)
    var zthick = 20;   // spessore in profondita z

    this.vertices = [];
    this.faces = [];

    var color = '#FFFFFF'; // bianco uniforme per tutta la scritta

    function addTube(a, b) {
        var Ax = a.x * scale, Ay = a.y * scale, Az = (a.z || 0) * scale;
        var Bx = b.x * scale, By = b.y * scale, Bz = (b.z || 0) * scale;
        var dx = Bx - Ax;
        var dy = By - Ay;
        var len = Math.sqrt(dx * dx + dy * dy);
        if (len === 0) return;
        var px = (-dy / len) * thick;
        var py = (dx / len) * thick;
        var dz = zthick;

        // 8 vertici: 4 anteriori (z+dz) + 4 posteriori (z-dz)
        var i0 = this.vertices.length;
        this.vertices.push(
            {x: Ax + px, y: Ay + py, z: Az + dz},
            {x: Ax - px, y: Ay - py, z: Az + dz},
            {x: Bx + px, y: By + py, z: Bz + dz},
            {x: Bx - px, y: By - py, z: Bz + dz},
            {x: Ax + px, y: Ay + py, z: Az - dz},
            {x: Ax - px, y: Ay - py, z: Az - dz},
            {x: Bx + px, y: By + py, z: Bz - dz},
            {x: Bx - px, y: By - py, z: Bz - dz}
        );

        // 12 facce del tubo, disabilitiamo culling per vedere entrambi i lati
        var faces = [
            {indices: [i0, i0 + 2, i0 + 1], color: color, cull: false},
            {indices: [i0 + 2, i0 + 3, i0 + 1], color: color, cull: false},
            {indices: [i0 + 4, i0 + 5, i0 + 6], color: color, cull: false},
            {indices: [i0 + 5, i0 + 7, i0 + 6], color: color, cull: false},
            {indices: [i0, i0 + 4, i0 + 6], color: color, cull: false},
            {indices: [i0, i0 + 6, i0 + 2], color: color, cull: false},
            {indices: [i0 + 1, i0 + 3, i0 + 5], color: color, cull: false},
            {indices: [i0 + 3, i0 + 7, i0 + 5], color: color, cull: false},
            {indices: [i0 + 2, i0 + 6, i0 + 3], color: color, cull: false},
            {indices: [i0 + 3, i0 + 6, i0 + 7], color: color, cull: false},
            {indices: [i0, i0 + 1, i0 + 5], color: color, cull: false},
            {indices: [i0, i0 + 5, i0 + 4], color: color, cull: false}
        ];
        for (var i = 0; i < faces.length; i++) {
            this.faces.push(faces[i]);
        }
    }

    var self = this;
    function seg(a, b) {
        addTube.call(self, a, b);
    }

    // ========== d ==========
    seg({x:-220, y:-40, z:0}, {x:-220, y:40,  z:0});
    var dArc = [
        {x:-220, y:-40, z:0}, {x:-205, y:-40, z:0},
        {x:-195, y:-24, z:0}, {x:-195, y:0,   z:0},
        {x:-195, y:24,  z:0}, {x:-205, y:40,  z:0},
        {x:-220, y:40,  z:0}
    ];
    for (var i = 0; i < dArc.length - 1; i++) {
        seg(dArc[i], dArc[i+1]);
    }

    // ========== s ==========
    var sPath = [
        {x:-170, y:-40, z:0}, {x:-150, y:-40, z:0},
        {x:-140, y:-24, z:0}, {x:-150, y:-8,  z:0},
        {x:-160, y:0,   z:0}, {x:-150, y:8,   z:0},
        {x:-140, y:24,  z:0}, {x:-150, y:40,  z:0},
        {x:-170, y:40,  z:0}
    ];
    for (var i = 0; i < sPath.length - 1; i++) {
        seg(sPath[i], sPath[i+1]);
    }

    // ========== M ==========
    seg({x:-120, y:-40, z:0}, {x:-120, y:40,  z:0});
    seg({x:-120, y:40,  z:0}, {x:-90,  y:-40, z:0});
    seg({x:-90,  y:-40, z:0}, {x:-60,  y:40,  z:0});
    seg({x:-60,  y:40,  z:0}, {x:-60,  y:-40, z:0});

    // ========== v ==========
    var vPath = [
        {x:-20, y:-40, z:0}, {x:-5, y:0,  z:0},
        {x:10,  y:40,  z:0}, {x:25, y:0,  z:0},
        {x:40,  y:-40, z:0}
    ];
    for (var i = 0; i < vPath.length - 1; i++) {
        seg(vPath[i], vPath[i+1]);
    }

    // ========== c ==========
    var cPath = [
        {x:120, y:40,  z:0}, {x:100, y:30,  z:0},
        {x:90,  y:10,  z:0}, {x:90,  y:-10, z:0},
        {x:100, y:-30, z:0}, {x:120, y:-40, z:0}
    ];
    for (var i = 0; i < cPath.length - 1; i++) {
        seg(cPath[i], cPath[i+1]);
    }
};

cdsMvc.prototype = new canvas3d.DisplayObject3D();
