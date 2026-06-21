var cSphere = function(size, latBands, lonBands) {
    size = size || 100;
    latBands = latBands || 12;
    lonBands = lonBands || 12;

    var colors = ['#00FF00','#FF0000','#0000FF','#00FFFF','#FFFF00','#FF00FF'];

    this.vertices = [];
    this.faces = [];

    for (var lat = 0; lat <= latBands; lat++) {
        var theta = lat * Math.PI / latBands;
        var sinTheta = Math.sin(theta);
        var cosTheta = Math.cos(theta);

        for (var lon = 0; lon <= lonBands; lon++) {
            var phi = lon * 2 * Math.PI / lonBands;
            var sinPhi = Math.sin(phi);
            var cosPhi = Math.cos(phi);

            var x = cosPhi * sinTheta;
            var y = cosTheta;
            var z = sinPhi * sinTheta;

            this.vertices.push(this.make3DPoint(x * size, y * size, z * size));
        }
    }

    for (var lat = 0; lat < latBands; lat++) {
        for (var lon = 0; lon < lonBands; lon++) {
            var first = (lat * (lonBands + 1)) + lon;
            var second = first + lonBands + 1;
            var color = colors[(lat + lon) % colors.length];

            this.faces.push({
                indices: [first, second, first + 1],
                color: color,
                wireframe: true
            });

            this.faces.push({
                indices: [second, second + 1, first + 1],
                color: color,
                wireframe: true
            });
        }
    }
};

cSphere.prototype = new canvas3d.DisplayObject3D();
