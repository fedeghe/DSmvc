/**
 | STL Loader for canvas3d engine
 | Supports both ASCII and Binary STL formats.
 | Deduplicates vertices automatically.
 |
 | Options: {color: '#RRGGBB', scale: 1.0}
 |   color: uniform fill color for all faces (default uses cyclic palette)
 |   scale: multiplier applied to all vertex coordinates
 |
 | Usage:
 |   var loader = new STLLoader({color: '#FFDAB9', scale: 4});
 |   loader.load('/models/soap.stl', function(model) {
 |       item.addChild(model);
 |   });
 */

var STLLoader = function(options) {
    this.options = options || {};
    this.uniformColor = this.options.color || null;
    this.scale = typeof this.options.scale === 'number' ? this.options.scale : 1;
};

STLLoader.prototype.load = function(url, callback) {
    var self = this;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.responseType = 'arraybuffer';
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        var model = self.parse(xhr.response);
        if (callback) callback(model);
    };
    xhr.send(null);
};

STLLoader.prototype.parse = function(arrayBuffer) {
    var isBinary = this.isBinary(arrayBuffer);
    if (isBinary) {
        return this.parseBinary(arrayBuffer);
    } else {
        return this.parseASCII(arrayBuffer);
    }
};

STLLoader.prototype.isBinary = function(arrayBuffer) {
    var reader = new DataView(arrayBuffer);
    var faceSize = (32 / 8 * 3) + ((32 / 8 * 3) * 3) + (16 / 8);
    var numFaces = reader.getUint32(80, true);
    var expectedSize = 80 + 4 + (numFaces * faceSize);
    return expectedSize === arrayBuffer.byteLength;
};

STLLoader.prototype.parseBinary = function(arrayBuffer) {
    var reader = new DataView(arrayBuffer);
    var numTriangles = reader.getUint32(80, true);
    var offset = 84;
    var scale = this.scale;

    var vertices = [];
    var vertexMap = {};
    var faces = [];

    function addVertex(x, y, z) {
        var key = x.toFixed(4) + ':' + y.toFixed(4) + ':' + z.toFixed(4);
        if (vertexMap.hasOwnProperty(key)) {
            return vertexMap[key];
        }
        var idx = vertices.length;
        vertices.push({x: x * scale, y: y * scale, z: z * scale});
        vertexMap[key] = idx;
        return idx;
    }

    for (var i = 0; i < numTriangles; i++) {
        offset += 12;

        var v0 = {
            x: reader.getFloat32(offset, true),
            y: reader.getFloat32(offset + 4, true),
            z: reader.getFloat32(offset + 8, true)
        };
        offset += 12;

        var v1 = {
            x: reader.getFloat32(offset, true),
            y: reader.getFloat32(offset + 4, true),
            z: reader.getFloat32(offset + 8, true)
        };
        offset += 12;

        var v2 = {
            x: reader.getFloat32(offset, true),
            y: reader.getFloat32(offset + 4, true),
            z: reader.getFloat32(offset + 8, true)
        };
        offset += 12;

        offset += 2;

        var i0 = addVertex(v0.x, v0.y, v0.z);
        var i1 = addVertex(v1.x, v1.y, v1.z);
        var i2 = addVertex(v2.x, v2.y, v2.z);

        faces.push({
            indices: [i0, i1, i2],
            color: this.computeColor(i),
            wireframe: true,
            cull: true
        });
    }

    var model = new canvas3d.DisplayObject3D();
    model.vertices = vertices;
    model.faces = faces;
    return model;
};

STLLoader.prototype.parseASCII = function(arrayBuffer) {
    var decoder = new TextDecoder('ascii');
    var text = decoder.decode(arrayBuffer);
    var scale = this.scale;

    var vertices = [];
    var vertexMap = {};
    var faces = [];

    function addVertex(x, y, z) {
        var key = x.toFixed(4) + ':' + y.toFixed(4) + ':' + z.toFixed(4);
        if (vertexMap.hasOwnProperty(key)) {
            return vertexMap[key];
        }
        var idx = vertices.length;
        vertices.push({x: x * scale, y: y * scale, z: z * scale});
        vertexMap[key] = idx;
        return idx;
    }

    var vertexRegex = /vertex\s+([\d.eE+-]+)\s+([\d.eE+-]+)\s+([\d.eE+-]+)/g;
    var match;
    var triVerts = [];
    var faceCount = 0;

    while ((match = vertexRegex.exec(text)) !== null) {
        var x = parseFloat(match[1]);
        var y = parseFloat(match[2]);
        var z = parseFloat(match[3]);
        triVerts.push({x: x, y: y, z: z});

        if (triVerts.length === 3) {
            var i0 = addVertex(triVerts[0].x, triVerts[0].y, triVerts[0].z);
            var i1 = addVertex(triVerts[1].x, triVerts[1].y, triVerts[1].z);
            var i2 = addVertex(triVerts[2].x, triVerts[2].y, triVerts[2].z);

            faces.push({
                indices: [i0, i1, i2],
                color: this.computeColor(faceCount),
                wireframe: true,
                cull: true
            });

            faceCount++;
            triVerts = [];
        }
    }

    var model = new canvas3d.DisplayObject3D();
    model.vertices = vertices;
    model.faces = faces;
    return model;
};

STLLoader.prototype.computeColor = function(idx) {
    if (this.uniformColor) return this.uniformColor;
    var palette = ['#00FF00','#FF0000','#0000FF','#00FFFF','#FFFF00','#FF00FF','#FFFFFF','#888888'];
    return palette[idx % palette.length];
};
