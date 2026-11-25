import { loadFont } from "@remotion/fonts";
import { AbsoluteFill, Audio, Easing, Img, interpolate, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { Grain } from "./BlackFridayDark";

// "By design" moodboard reel: a cream paper board with fabric swatches, traced line sketches that
// draw themselves on, a pinned photo of the finished look and the brand line. One slow camera move.

loadFont({ family: "Caveat", url: staticFile("bd/caveat.woff2"), weight: "500" });
loadFont({ family: "CormorantI", url: staticFile("bd/corm-italic.woff2"), weight: "500" });
loadFont({ family: "Cormorant", url: staticFile("bd/corm-500.woff2"), weight: "500" });

export type ByDesignProps = {
  sketchFront: string;
  sketchBack: string;
  pin: string;
  detail: string;
  swatches: [string, string, string];
  note: [string, string, string];
  audio: string | null;
};

const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;
const ease = Easing.inOut(Easing.cubic);

// camera keyframes: [time s, centre x, centre y, zoom] in board coordinates (board = 1080 x 1920)
const CAM: [number, number, number, number][] = [
  [0, 880, 420, 2.1],
  [2.6, 880, 430, 2.0],
  [4.4, 870, 1110, 2.0],
  [6.0, 870, 1120, 1.95],
  [7.6, 300, 1420, 1.7],
  [9.0, 470, 900, 1.25],
  [11.0, 540, 960, 1.0],
  [99, 540, 960, 1.0],
];
const cam = (t: number) => {
  const ts = CAM.map((k) => k[0]);
  const f = (i: number) => interpolate(t, ts, CAM.map((k) => k[i]), { ...clamp, easing: ease });
  return { x: f(1), y: f(2), z: f(3) };
};

const pinking = "polygon(" + Array.from({ length: 41 }, (_, i) => `${(i / 40) * 100}% ${i % 2 ? 0 : 3}%`).join(",") + "," +
  Array.from({ length: 41 }, (_, i) => `${100 - (i / 40) * 100}% ${i % 2 ? 100 : 97}%`).join(",") + ")";

const Card: React.FC<{ x: number; y: number; w: number; h: number; rot: number; pad?: number; children: React.ReactNode; shadow?: boolean }> = ({
  x, y, w, h, rot, pad = 0, children, shadow = true,
}) => (
  <div style={{ position: "absolute", left: x, top: y, width: w, height: h, transform: `rotate(${rot}deg)`, background: "#FBF8F2", padding: pad,
    boxShadow: shadow ? "0 6px 18px rgba(70,55,40,0.18), 0 1px 3px rgba(70,55,40,0.15)" : "none" }}>
    {children}
  </div>
);
const Pin: React.FC<{ x: number; y: number }> = ({ x, y }) => (
  <div style={{ position: "absolute", left: x - 11, top: y - 11, width: 22, height: 22, borderRadius: 11,
    background: "radial-gradient(circle at 35% 35%, #C9A26B, #7A5A33 70%)", boxShadow: "2px 4px 5px rgba(0,0,0,0.3)" }} />
);

const Sketch: React.FC<{ src: string; progress: number }> = ({ src, progress }) => (
  // draws on from top to bottom with a soft leading edge
  <div style={{ width: "100%", height: "100%", WebkitMaskImage: `linear-gradient(to bottom, #000 ${progress * 100 - 4}%, transparent ${progress * 100 + 4}%)`,
    maskImage: `linear-gradient(to bottom, #000 ${progress * 100 - 4}%, transparent ${progress * 100 + 4}%)` }}>
    <Img src={staticFile(src)} style={{ width: "100%", height: "100%", objectFit: "contain" }} />
  </div>
);

export const ByDesign: React.FC<ByDesignProps> = ({ sketchFront, sketchBack, pin, detail, swatches, note, audio }) => {
  const frame = useCurrentFrame();
  const { fps, width, height, durationInFrames } = useVideoConfig();
  const t = frame / fps;
  const c = cam(t);
  const front = interpolate(t, [0.4, 2.8], [0, 1], { ...clamp, easing: Easing.inOut(Easing.sin) });
  const back = interpolate(t, [3.6, 5.9], [0, 1], { ...clamp, easing: Easing.inOut(Easing.sin) });
  const pinIn = interpolate(t, [7.8, 8.6], [0, 1], { ...clamp, easing: Easing.out(Easing.cubic) });
  const noteIn = (i: number) => interpolate(t, [6.3 + i * 0.45, 6.8 + i * 0.45], [0, 1], clamp);
  const copyIn = (i: number) => interpolate(t, [9.6 + i * 0.5, 10.2 + i * 0.5], [0, 1], clamp);
  const sway = Math.sin(t * 0.8) * 1.2;

  return (
    <AbsoluteFill style={{ backgroundColor: "#EDE6DA", overflow: "hidden" }}>
      <div style={{ position: "absolute", left: 0, top: 0, width, height, transformOrigin: "0 0",
        transform: `translate(${width / 2 - c.x * c.z + sway}px, ${height / 2 - c.y * c.z}px) scale(${c.z})` }}>
        {/* paper board */}
        <div style={{ position: "absolute", inset: -200, background: "radial-gradient(ellipse at 40% 30%, #F4EFE6 0%, #ECE5D9 60%, #E2D9CB 100%)" }} />
        {/* swatches */}
        {swatches.map((s, i) => (
          <div key={s} style={{ position: "absolute", left: -30, top: 40 + i * 250, width: 330, height: 260, clipPath: pinking,
            transform: `rotate(${[-1.5, 1, -0.5][i]}deg)`, boxShadow: "0 4px 10px rgba(0,0,0,0.25)" }}>
            <Img src={staticFile(s)} style={{ width: "100%", height: "100%", objectFit: "cover" }} />
          </div>
        ))}
        {/* sketches */}
        <Card x={690} y={60} w={360} h={720} rot={1.4} pad={20}><Sketch src={sketchFront} progress={front} /></Card>
        <Card x={680} y={800} w={360} h={680} rot={-1} pad={20}><Sketch src={sketchBack} progress={back} /></Card>
        {/* detail photo */}
        <Card x={30} y={1180} w={400} h={420} rot={2} pad={14}><Img src={staticFile(detail)} style={{ width: "100%", height: "100%", objectFit: "cover" }} /></Card>
        <Pin x={230} y={1196} />
        {/* handwritten note */}
        <Card x={70} y={1640} w={470} h={230} rot={-3} shadow>
          <div style={{ padding: "30px 40px", fontFamily: "Caveat", fontSize: 54, lineHeight: 1.05, color: "#4A423A", letterSpacing: 3 }}>
            {note.map((n, i) => (
              <div key={n} style={{ opacity: noteIn(i), textDecoration: i === 2 ? "underline" : "none" }}>{n}</div>
            ))}
          </div>
        </Card>
        {/* pinned finished look */}
        <div style={{ opacity: pinIn, transform: `translateY(${(1 - pinIn) * -30}px) scale(${1.04 - 0.04 * pinIn})` }}>
          <Card x={300} y={320} w={400} h={560} rot={-1.2} pad={16}><Img src={staticFile(pin)} style={{ width: "100%", height: "100%", objectFit: "cover" }} /></Card>
          <Pin x={500} y={336} />
        </div>
        {/* brand line */}
        <div style={{ position: "absolute", left: 620, top: 1520, width: 440, color: "#2B2622" }}>
          <div style={{ opacity: copyIn(0), fontSize: 92, lineHeight: 1 }}>
            <span style={{ fontFamily: "CormorantI" }}>by</span><span style={{ fontFamily: "Cormorant", letterSpacing: 1 }}>DESIGN</span>
          </div>
          {["It starts with a sketch.", "Every piece, drawn.", "First, the idea."].map((l, i) => (
            <div key={l} style={{ opacity: copyIn(i + 1), fontFamily: "Cormorant", fontSize: 40, marginTop: i === 2 ? 22 : 8 }}>{l}</div>
          ))}
        </div>
      </div>
      <AbsoluteFill style={{ background: "radial-gradient(ellipse 85% 75% at 50% 50%, rgba(0,0,0,0) 60%, rgba(40,30,20,0.25) 100%)" }} />
      <Grain />
      {audio ? (
        <Audio src={staticFile(audio)} volume={(f) => interpolate(f, [durationInFrames - 12, durationInFrames], [1, 0], clamp)} />
      ) : null}
    </AbsoluteFill>
  );
};
