# MST "Kitap perisi" maskotu — Blender (bpy) ile modelleme + poz render
# Çalıştırma: pip install bpy pillow; python3 araclar/peri-blender.py  (PNG çıktıları bu klasöre yazılır;
# sayfadaki görseller bunlardan kırpılıp 360px WebP olarak mst-randevu/assets/peri/ altına konur)
import bpy, bmesh, math, sys, os
from mathutils import Vector

OUT = os.path.dirname(os.path.abspath(__file__))
POZLAR_ISTENEN = sys.argv[sys.argv.index('--') + 1:] if '--' in sys.argv else []
R = math.radians


def hexrgb(h, a=1.0):
    h = h.lstrip('#')
    c = [int(h[i:i + 2], 16) / 255 for i in (0, 2, 4)]
    c = [x / 12.92 if x <= 0.04045 else ((x + 0.055) / 1.055) ** 2.4 for x in c]
    return (*c, a)


# ---------------- sahne ----------------
bpy.ops.wm.read_factory_settings(use_empty=True)
sc = bpy.context.scene
sc.render.engine = 'CYCLES'
sc.cycles.device = 'CPU'
sc.cycles.samples = 110
sc.cycles.use_denoising = True
sc.render.film_transparent = True
sc.render.resolution_x = sc.render.resolution_y = 900
sc.view_settings.view_transform = 'Standard'
sc.view_settings.look = 'None'
sc.render.image_settings.file_format = 'PNG'
sc.render.image_settings.color_mode = 'RGBA'

w = bpy.data.worlds.new('dunya'); sc.world = w
w.use_nodes = True
bg = w.node_tree.nodes['Background']
bg.inputs[0].default_value = hexrgb('#2a2622'); bg.inputs[1].default_value = 0.9


def mat(ad, renk, rough=0.5, metal=0.0, emis=None, emis_g=0.0, coat=0.0, alpha=1.0, trans=0.0):
    m = bpy.data.materials.new(ad); m.use_nodes = True
    p = m.node_tree.nodes['Principled BSDF']
    p.inputs['Base Color'].default_value = hexrgb(renk)
    p.inputs['Roughness'].default_value = rough
    p.inputs['Metallic'].default_value = metal
    p.inputs['Coat Weight'].default_value = coat
    p.inputs['Alpha'].default_value = alpha
    p.inputs['Transmission Weight'].default_value = trans
    if emis:
        p.inputs['Emission Color'].default_value = hexrgb(emis)
        p.inputs['Emission Strength'].default_value = emis_g
    return m


M = {
    'kapak': mat('kapak', '#e3a01c', 0.32, 0.35, coat=0.6),
    'cerceve': mat('cerceve', '#9a6206', 0.3, 0.6, coat=0.4),
    'sayfa': mat('sayfa', '#f6ead0', 0.75),
    'ten': mat('ten', '#f7ecd6', 0.55),
    'eldiven': mat('eldiven', '#fffaf0', 0.45, coat=0.3),
    'ayakkabi': mat('ayakkabi', '#2a2521', 0.35, coat=0.6),
    'goz': mat('goz', '#fffdf7', 0.2, coat=0.8),
    'bebek': mat('bebek', '#1b1712', 0.15, coat=1.0),
    'parilti': mat('parilti', '#ffffff', 0.1, emis='#ffffff', emis_g=4.0),
    'agiz': mat('agiz', '#3a2106', 0.4),
    'agiz_ic': mat('agiz_ic', '#6b1c14', 0.5),
    'dil': mat('dil', '#f07b6a', 0.5),
    'yanak': mat('yanak', '#f27f55', 0.6, alpha=0.75),
    'kurdele': mat('kurdele', '#7d1419', 0.45, coat=0.4),
    'kanat': None,
    'asa': mat('asa', '#3a2f24', 0.3, coat=0.8),
    'yildiz': mat('yildiz', '#ffc83a', 0.25, 0.4, emis='#ffb000', emis_g=2.2),
}

# Kanat: kenarları parlayan, içi şeffaf
km = bpy.data.materials.new('kanat'); km.use_nodes = True; kn = km.node_tree.nodes; kl = km.node_tree.links
kn.clear()
o_ = kn.new('ShaderNodeOutputMaterial'); mx = kn.new('ShaderNodeMixShader'); tr_ = kn.new('ShaderNodeBsdfTransparent')
ad_ = kn.new('ShaderNodeAddShader'); em = kn.new('ShaderNodeEmission'); gl = kn.new('ShaderNodeBsdfGlossy')
lw = kn.new('ShaderNodeLayerWeight'); lw.inputs['Blend'].default_value = 0.35
mr = kn.new('ShaderNodeMapRange'); mr.inputs['To Min'].default_value = 0.3; mr.inputs['To Max'].default_value = 0.95
em.inputs['Color'].default_value = hexrgb('#ffc640'); em.inputs['Strength'].default_value = 1.0
gl.inputs['Roughness'].default_value = 0.15; gl.inputs['Color'].default_value = hexrgb('#ffe2a0')
kl.new(lw.outputs['Facing'], mr.inputs['Value']); kl.new(mr.outputs['Result'], mx.inputs['Fac'])
kl.new(tr_.outputs[0], mx.inputs[1]); kl.new(em.outputs[0], ad_.inputs[0]); kl.new(gl.outputs[0], ad_.inputs[1])
kl.new(ad_.outputs[0], mx.inputs[2]); kl.new(mx.outputs[0], o_.inputs['Surface'])
M['kanat'] = km

# Sayfa kenarındaki ince çizgiler
sn = M['sayfa'].node_tree
tc = sn.nodes.new('ShaderNodeTexCoord'); wv = sn.nodes.new('ShaderNodeTexWave')
wv.wave_type = 'BANDS'; wv.bands_direction = 'Y'; wv.inputs['Scale'].default_value = 45
wv.inputs['Distortion'].default_value = 0.5
mix = sn.nodes.new('ShaderNodeMix'); mix.data_type = 'RGBA'
mix.inputs['Factor'].default_value = 0.18
mix.inputs['A'].default_value = hexrgb('#f6ead0'); mix.inputs['B'].default_value = hexrgb('#c8ad7c')
sn.links.new(tc.outputs['Object'], wv.inputs['Vector'])
sn.links.new(wv.outputs['Color'], mix.inputs['Factor'])
sn.links.new(mix.outputs['Result'], sn.nodes['Principled BSDF'].inputs['Base Color'])
mix.inputs['Factor'].default_value = 0.0


def bos(ad, parent=None, loc=(0, 0, 0)):
    o = bpy.data.objects.new(ad, None); sc.collection.objects.link(o)
    o.parent = parent; o.location = loc
    return o


def yerlestir(o, parent, loc, rot=(0, 0, 0), scale=None, m=None, bevel=0, seg=4, smooth=True):
    o.parent = parent; o.location = loc; o.rotation_euler = rot
    if scale: o.scale = scale
    if m: o.data.materials.append(M[m])
    if bevel:
        b = o.modifiers.new('bv', 'BEVEL'); b.width = bevel; b.segments = seg; b.limit_method = 'ANGLE'
    if smooth:
        for p in o.data.polygons: p.use_smooth = True
    return o


def kutu(ad, parent, loc, boyut, m, bevel=0.02):
    bpy.ops.mesh.primitive_cube_add(size=1)
    o = bpy.context.object; o.name = ad
    o.data.transform(__import__('mathutils').Matrix.Diagonal((*boyut, 1)))
    yerlestir(o, parent, loc, m=m, bevel=bevel, seg=5, smooth=False)
    o.data.polygons.foreach_set('use_smooth', [True] * len(o.data.polygons))
    o.modifiers.new('wn', 'WEIGHTED_NORMAL').keep_sharp = True
    return o


def kure(ad, parent, loc, scale, m, seg=48):
    bpy.ops.mesh.primitive_uv_sphere_add(segments=seg, ring_count=seg // 2, radius=1)
    o = bpy.context.object; o.name = ad
    return yerlestir(o, parent, loc, scale=scale, m=m)


def silindir(ad, parent, loc, r, uzun, m, rot=(0, 0, 0)):
    bpy.ops.mesh.primitive_cylinder_add(vertices=40, radius=r, depth=uzun)
    o = bpy.context.object; o.name = ad
    return yerlestir(o, parent, loc, rot=rot, m=m)


def yay(ad, parent, loc, R_, r, m, ust=True, rot=(R(90), 0, 0), kes=0.0):
    """Yarım halka: ust=True -> ^ şekli, False -> ‿ şekli"""
    bpy.ops.mesh.primitive_torus_add(major_radius=R_, minor_radius=r, major_segments=64, minor_segments=16)
    o = bpy.context.object; o.name = ad
    bm = bmesh.new(); bm.from_mesh(o.data)
    sil = [v for v in bm.verts if (v.co.y < kes - 1e-4 if ust else v.co.y > -kes + 1e-4)]
    bmesh.ops.delete(bm, geom=sil, context='VERTS'); bm.to_mesh(o.data); bm.free()
    return yerlestir(o, parent, loc, rot=rot, m=m)


def yildiz_mesh(ad, parent, loc, boy, m, kalin=0.05):
    me = bpy.data.meshes.new(ad); bm = bmesh.new()
    ust, alt = [], []
    for i in range(10):
        a = math.pi / 2 + i * math.pi / 5
        rr = boy if i % 2 == 0 else boy * 0.45
        ust.append(bm.verts.new((rr * math.cos(a), -kalin / 2, rr * math.sin(a))))
        alt.append(bm.verts.new((rr * math.cos(a), kalin / 2, rr * math.sin(a))))
    on = bm.verts.new((0, -kalin * 1.6, 0)); ar = bm.verts.new((0, kalin * 1.6, 0))
    for i in range(10):
        j = (i + 1) % 10
        bm.faces.new((on, ust[i], ust[j])); bm.faces.new((ar, alt[j], alt[i]))
        bm.faces.new((ust[i], alt[i], alt[j], ust[j]))
    bmesh.ops.recalc_face_normals(bm, faces=bm.faces)
    bm.to_mesh(me); bm.free()
    o = bpy.data.objects.new(ad, me); sc.collection.objects.link(o)
    yerlestir(o, parent, loc, m=m, smooth=False)
    b = o.modifiers.new('bv', 'BEVEL'); b.width = 0.012; b.segments = 3
    return o


# ---------------- karakter ----------------
kok = bos('kok')
govde = bos('govde', kok)

# Kitap
kutu('on_kapak', govde, (0.01, -0.18, 0), (1.1, 0.055, 1.4), 'kapak', 0.028)
kutu('arka_kapak', govde, (0.01, 0.18, 0), (1.1, 0.055, 1.4), 'kapak', 0.028)
kutu('sirt', govde, (-0.51, 0, 0), (0.1, 0.415, 1.4), 'kapak', 0.05)
kutu('sayfalar', govde, (0.0, 0, 0), (1.0, 0.31, 1.3), 'sayfa', 0.012)
for z in (0.5, -0.5):
    kutu('sirt_bant', govde, (-0.55, 0, z), (0.02, 0.36, 0.05), 'cerceve', 0.008)
# Kapak çerçevesi
cz, cx, t = 0.56, 0.41, 0.028
for loc, b in [((0, -0.21, cz), (2 * cx + t, 0.012, t)), ((0, -0.21, -cz), (2 * cx + t, 0.012, t)),
               ((-cx, -0.21, 0), (t, 0.012, 2 * cz)), ((cx, -0.21, 0), (t, 0.012, 2 * cz))]:
    kutu('cerceve', govde, loc, b, 'cerceve', 0.006)
yildiz_mesh('amblem', govde, (0, -0.215, 0.44), 0.06, 'yildiz', 0.02)
# Kurdele (sayfaların arasından yukarı kıvrılan ayraç)
cv = bpy.data.curves.new('kurdele', 'CURVE'); cv.dimensions = '3D'
cv.extrude = 0.05; cv.bevel_depth = 0.0; cv.twist_mode = 'MINIMUM'
sp = cv.splines.new('BEZIER'); sp.bezier_points.add(2)
for bp, co in zip(sp.bezier_points, [(0.22, 0.0, 0.6), (0.24, 0.0, 0.86), (0.42, 0.0, 0.98)]):
    bp.co = co; bp.handle_left_type = bp.handle_right_type = 'AUTO'
ko = bpy.data.objects.new('kurdele', cv); sc.collection.objects.link(ko); ko.parent = govde
cv.materials.append(M['kurdele'])

# Yüz
YUZ = {}
ey = -0.215
for s, x in (('L', -0.2), ('R', 0.2)):
    YUZ['goz' + s] = kure('goz' + s, govde, (x, ey, 0.17), (0.135, 0.045, 0.165), 'goz')
    YUZ['bebek' + s] = kure('bebek' + s, govde, (x, ey - 0.03, 0.15), (0.085, 0.03, 0.1), 'bebek')
    YUZ['par' + s] = kure('par' + s, govde, (x + 0.03, ey - 0.06, 0.19), (0.028, 0.012, 0.03), 'parilti')
    YUZ['par2' + s] = kure('par2' + s, govde, (x - 0.025, ey - 0.06, 0.12), (0.013, 0.008, 0.013), 'parilti')
    YUZ['mutlu' + s] = yay('mutlu' + s, govde, (x, ey - 0.01, 0.13), 0.1, 0.022, 'agiz', ust=True)
    YUZ['yanak' + s] = kure('yanak' + s, govde, (x * 1.62, ey + 0.005, -0.03), (0.07, 0.012, 0.045), 'yanak')
YUZ['gulus'] = yay('gulus', govde, (0, ey - 0.005, -0.06), 0.085, 0.02, 'agiz', ust=False)
# Açık ağız (D şekli)
bpy.ops.mesh.primitive_cylinder_add(vertices=48, radius=0.1, depth=0.03)
ag = bpy.context.object; ag.name = 'acik_agiz'
bm = bmesh.new(); bm.from_mesh(ag.data)
bmesh.ops.delete(bm, geom=[v for v in bm.verts if v.co.y > 1e-4], context='VERTS')
bmesh.ops.contextual_create(bm, geom=[e for e in bm.edges if e.is_boundary])
bm.to_mesh(ag.data); bm.free()
yerlestir(ag, govde, (0, ey + 0.005, -0.05), rot=(R(90), 0, 0), m='agiz_ic')
YUZ['acik_agiz'] = ag
YUZ['dil'] = kure('dil', govde, (0, ey - 0.012, -0.12), (0.05, 0.01, 0.028), 'dil')

# Kollar
KOL = {}
for s, sx in (('L', -1), ('R', 1)):
    omuz = bos('omuz' + s, govde, (sx * 0.545, -0.02, 0.02))
    silindir('ust_kol' + s, omuz, (0, 0, -0.12), 0.036, 0.24, 'ten')
    kure('omuz_top' + s, omuz, (0, 0, 0), (0.045,) * 3, 'ten', 24)
    dirsek = bos('dirsek' + s, omuz, (0, 0, -0.24))
    kure('dirsek_top' + s, dirsek, (0, 0, 0), (0.037,) * 3, 'ten', 24)
    silindir('alt_kol' + s, dirsek, (0, 0, -0.1), 0.034, 0.2, 'ten')
    el = bos('el' + s, dirsek, (0, 0, -0.22))
    kure('eldiven' + s, el, (0, 0, -0.02), (0.085, 0.07, 0.085), 'eldiven')
    kure('bas_parmak' + s, el, (-sx * 0.07, -0.03, 0.01), (0.035, 0.03, 0.045), 'eldiven', 24)
    KOL[s] = (omuz, dirsek, el)

# Asa (el nesnesine bağlanır)
asa = bos('asa')
silindir('asa_sap', asa, (0, 0, 0.19), 0.017, 0.46, 'asa')
kure('asa_uc', asa, (0, 0, -0.04), (0.024,) * 3, 'yildiz', 16)
yildiz_mesh('asa_yildiz', asa, (0, 0, 0.52), 0.16, 'yildiz', 0.06)

# Bacaklar
for s, sx in (('L', -1), ('R', 1)):
    kalca = bos('kalca' + s, govde, (sx * 0.22, 0, -0.66))
    silindir('bacak' + s, kalca, (0, 0, -0.13), 0.036, 0.26, 'ten')
    kure('ayak' + s, kalca, (0, -0.05, -0.28), (0.085, 0.13, 0.065), 'ayakkabi')
    kalca.rotation_euler = (R(-12), sx * R(6), 0)

# Kanatlar
KANAT = []
for s, sx in (('L', -1), ('R', 1)):
    for i, (boy, en, ac, dz) in enumerate([(0.62, 0.3, 32, 0.25), (0.42, 0.21, -22, -0.05)]):
        piv = bos('kanat_piv' + s + str(i), govde, (sx * 0.28, 0.24, dz))
        k = kure('kanat' + s + str(i), piv, (sx * boy * 0.9, 0, 0), (boy, 0.012, en), 'kanat', 40)
        dmr = kure('damar' + s + str(i), piv, (sx * boy * 0.9, -0.004, 0), (boy * 0.82, 0.004, en * 0.78), 'kanat', 40)
        dmr.hide_render = True
        piv.rotation_euler = (0, -sx * R(ac), sx * R(-18))
        KANAT.append((piv, sx, ac))

# Kamera ve ışıklar
cam_d = bpy.data.cameras.new('kam'); cam_d.lens = 55
cam = bpy.data.objects.new('kam', cam_d); sc.collection.objects.link(cam); sc.camera = cam
cam.location = (0.0, -5.1, 1.35)
hedef = bos('hedef', None, (0, 0, 0.12))
tr = cam.constraints.new('TRACK_TO'); tr.target = hedef; tr.track_axis = 'TRACK_NEGATIVE_Z'; tr.up_axis = 'UP_Y'


def isik(ad, loc, guc, boy, renk='#ffffff'):
    d = bpy.data.lights.new(ad, 'AREA'); d.energy = guc; d.size = boy; d.color = hexrgb(renk)[:3]
    o = bpy.data.objects.new(ad, d); sc.collection.objects.link(o); o.location = loc
    c = o.constraints.new('TRACK_TO'); c.target = hedef; c.track_axis = 'TRACK_NEGATIVE_Z'; c.up_axis = 'UP_Y'


isik('ana', (-2.6, -3.4, 3.0), 750, 3.0, '#fff4e2')
isik('dolgu', (3.2, -2.6, 0.4), 260, 3.0, '#ffe7c4')
isik('kontur', (1.8, 3.2, 2.6), 900, 2.0, '#ffd27a')
isik('kontur2', (-2.4, 2.6, 0.8), 500, 2.0, '#fff1d6')

# ---------------- pozlar ----------------
# kol: (dışa açılma, öne, dirsek bükümü) derece; asa: (hangi el, asanın ele göre açısı) veya None
POZLAR = {
    'selam': dict(L=(35, 10, -15), R=(150, 5, -35), asa=('L', (-25,)), yuz='acik', bak=(0, 0), govde=(0, 0, -30), kanat=1.0),
    'goster': dict(L=(88, 18, -8), R=(22, 5, -20), asa=('L', (-80,)), yuz='gulus', bak=(-0.035, 0.005), govde=(0, 7, -26), kanat=0.8),
    'dusun': dict(L=(30, 60, -125), R=(30, 5, -10), asa=('R', (35,)), yuz='gulus', bak=(0.03, 0.04), govde=(0, -6, -32), kanat=0.6),
    'sevinc': dict(L=(150, 5, 10), R=(150, 5, 10), asa=('R', (15,)), yuz='mutlu_acik', bak=(0, 0), govde=(0, 0, -28), kanat=1.3),
    'goz_kirp': dict(L=(40, 10, -20), R=(125, 25, -60), asa=('R', (30,)), yuz='kirp', bak=(0, 0), govde=(0, -5, -32), kanat=1.0),
}


def poz_uygula(p):
    for s, sx in (('L', -1), ('R', 1)):
        aci, one, buk = p[s]
        omuz, dirsek, el = KOL[s]
        # sol kolda +Y dışa, sağ kolda -Y dışa döndürür
        omuz.rotation_euler = (R(-one), -sx * R(aci), 0)
        dirsek.rotation_euler = (0, -sx * R(buk), 0)
    ek, arot = p['asa']
    sx = -1 if ek == 'L' else 1
    rx, ry, rz = p['govde']
    kok.rotation_euler = (R(rx), R(ry), R(rz))
    bpy.context.view_layer.update()
    el = KOL[ek][2]
    asa.parent = None
    from mathutils import Matrix, Euler
    konum = el.matrix_world.translation + Vector((0, -0.06, -0.02))
    # arot = asanın yukarıdan sapma açısı (derece, + izleyicinin sağına), öne eğim
    rot = Euler((R(-arot[1] if len(arot) > 1 else 0), R(arot[0]), 0)).to_matrix().to_4x4()
    asa.matrix_world = Matrix.Translation(konum) @ rot
    for k in YUZ.values():
        k.hide_render = True
    acik = ['gozL', 'gozR', 'bebekL', 'bebekR', 'parL', 'parR', 'par2L', 'par2R']
    goster = {'acik': acik + ['acik_agiz', 'dil'], 'gulus': acik + ['gulus'],
              'mutlu_acik': ['mutluL', 'mutluR', 'acik_agiz', 'dil'],
              'kirp': ['gozL', 'bebekL', 'parL', 'par2L', 'mutluR', 'gulus']}[p['yuz']]
    for ad in goster + ['yanakL', 'yanakR']:
        YUZ[ad].hide_render = False
    dx, dz = p['bak']
    for s, x in (('L', -0.2), ('R', 0.2)):
        YUZ['bebek' + s].location = (x + dx, ey - 0.03, 0.15 + dz)
        YUZ['par' + s].location = (x + 0.03 + dx, ey - 0.06, 0.19 + dz)
        YUZ['par2' + s].location = (x - 0.025 + dx, ey - 0.06, 0.12 + dz)
    rx, ry, rz = p['govde']
    kok.rotation_euler = (R(rx), R(ry), R(rz))
    for piv, sx, ac in KANAT:
        piv.rotation_euler = (0, -sx * R(ac * p['kanat']), sx * R(-18))


for ad, p in POZLAR.items():
    if POZLAR_ISTENEN and ad not in POZLAR_ISTENEN:
        continue
    poz_uygula(p)
    sc.render.filepath = os.path.join(OUT, ad + '.png')
    bpy.ops.render.render(write_still=True)
    print('RENDER', ad)
