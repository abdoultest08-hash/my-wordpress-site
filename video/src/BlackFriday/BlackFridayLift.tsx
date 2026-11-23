import { loadFont } from "@remotion/fonts";
import {
  AbsoluteFill,
  Audio,
  Easing,
  Img,
  interpolate,
  random,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";

// Black Friday reel: the lift doors in frame A slide open to reveal frame B (the lift packed
// with Celéste mailer bags). Both stills share the same camera, so the door leaves are cut out
// of frame A and slid apart over frame B.

const SERIF = "Cormorant Garamond";
loadFont({ family: SERIF, url: staticFile("bf/f1.woff2"), weight: "500" });

// Stills are 1536x2736; output is 1080x1920
const S = 1080 / 1536;
const DOOR = { left: 317 * S, right: 1219 * S, top: 327 * S, bottom: 2200 * S, seam: 768 * S };

const IVORY = "#F6F1E9";

export type BlackFridayProps = {
  discount: number;
  audio: string | null;
  // Seconds at which the text changes (on the soundtrack's own cut points)
  lineTimes: [number, number];
  // Second at which bags start tumbling out of the lift
  spillStart: number;
};

const DoorLeaf: React.FC<{ side: "left" | "right"; open: number }> = ({ side, open }) => {
  const width = side === "left" ? DOOR.seam - DOOR.left : DOOR.right - DOOR.seam;
  const x = side === "left" ? DOOR.left : DOOR.seam;
  return (
    <div
      style={{
        position: "absolute",
        left: x - DOOR.left,
        top: 0,
        width,
        height: DOOR.bottom - DOOR.top,
        overflow: "hidden",
        transform: `translateX(${(side === "left" ? -1 : 1) * open * width}px)`,
      }}
    >
      <Img
        src={staticFile("bf/frame-A.png")}
        style={{ position: "absolute", left: -x, top: -DOOR.top, width: 1080, height: 1920 }}
      />
    </div>
  );
};

// One mailer bag tipping out of the bottom of the stack, falling onto the marble and sliding
// towards the camera (it grows as it comes closer and tips flat as it lands).
const FallingBag: React.FC<{ index: number; start: number }> = ({ index, start }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const r = (k: string) => random(`bag-${index}-${k}`);
  const t0 = start + index * 0.14 + r("delay") * 0.07;
  const t = frame / fps - t0;
  if (t < 0) return null;

  const fall = 0.5 + r("fall") * 0.2; // seconds until it hits the floor
  const x0 = DOOR.left + 90 + r("x") * (DOOR.right - DOOR.left - 180);
  const y0 = 1300 + r("y") * 170;
  const spread = (x0 - DOOR.seam) * (0.5 + r("spread") * 0.8) + (r("drift") - 0.5) * 220;
  const near = r("near"); // how far towards the camera it ends up
  // lands in front of the lift; later bags land on top of the growing pile
  const yLand = 1600 + near * 170 - Math.min(index, 20) * 6;
  const size0 = 255;
  const sizeLand = 300 + near * 170;

  const u = Math.min(t / fall, 1);
  const g = u * u; // gravity: accelerating fall
  const slide = interpolate(t, [fall, fall + 0.9], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.out(Easing.quad),
  });
  const bounce = t > fall ? Math.sin(Math.min((t - fall) / 0.22, 1) * Math.PI) * (18 + near * 14) : 0;

  const x = x0 + spread * (0.55 * g + 0.45 * slide);
  const y = y0 + (yLand - y0) * g + slide * (15 + near * 35) - bounce;
  const size = size0 + (sizeLand - size0) * g + slide * near * 40;
  const spin = (r("spin") - 0.5) * 70 * (0.6 * g + 0.4 * slide);
  const tip = 46 * Math.min(1, g * 1.15); // falls forward and ends lying on the floor
  const speed = t < fall ? u : 1 - slide;
  const appear = interpolate(t, [0, 0.08], [0, 1], { extrapolateRight: "clamp" });

  return (
    <>
      {/* contact shadow on the floor */}
      <div
        style={{
          position: "absolute",
          left: x - size * 0.55,
          top: yLand + slide * (15 + near * 35) + size * 0.18,
          width: size * 1.1,
          height: size * 0.32,
          borderRadius: "50%",
          background: "radial-gradient(ellipse at center, rgba(60,45,30,0.38) 0%, rgba(60,45,30,0) 70%)",
          opacity: g * appear,
        }}
      />
      <div style={{ position: "absolute", left: x - size / 2, top: y - size / 2, width: size, height: size, perspective: 900, opacity: appear }}>
        <Img
          src={staticFile(index % 2 ? "bf/bag2.png" : "bf/bag1.png")}
          style={{
            width: "100%",
            height: "100%",
            transform: `rotateX(${tip}deg) rotateY(${(r("yaw") - 0.5) * 30}deg) rotateZ(${spin}deg)`,
            transformOrigin: "50% 100%",
            filter: `brightness(0.93) blur(${(speed * 1.6).toFixed(2)}px) drop-shadow(0 10px 14px rgba(60,45,30,0.25))`,
          }}
        />
      </div>
    </>
  );
};

const FallingBags: React.FC<{ start: number }> = ({ start }) => (
  <>
    {Array.from({ length: 26 }, (_, i) => (
      <FallingBag key={i} index={i} start={start} />
    ))}
  </>
);

const Line: React.FC<{ text: string; from: number; to: number; band: number }> = ({
  text,
  from,
  to,
  band,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const f0 = Math.round(from * fps);
  const f1 = Math.round(to * fps);
  if (frame < f0 - 1 || frame > f1) return null;
  // Quick fade + small rise in, quick fade out
  const fadeIn = interpolate(frame, [f0, f0 + 6], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const fadeOut = interpolate(frame, [f1 - 5, f1], [1, 0], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const rise = interpolate(frame, [f0, f0 + 8], [18, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.out(Easing.cubic),
  });
  return (
    <AbsoluteFill style={{ justifyContent: "center", alignItems: "center", opacity: Math.min(fadeIn, fadeOut) }}>
      {/* Soft mocha band so ivory text stays readable over the ivory bags */}
      <div
        style={{
          position: "absolute",
          left: -60,
          right: -60,
          height: 520,
          background: "radial-gradient(ellipse at center, rgba(52,42,33,0.55) 0%, rgba(52,42,33,0.28) 45%, rgba(52,42,33,0) 72%)",
          opacity: band,
        }}
      />
      <div
        style={{
          transform: `translateY(${rise}px)`,
          fontFamily: SERIF,
          fontWeight: 500,
          fontSize: 168,
          lineHeight: 0.98,
          letterSpacing: 6,
          color: IVORY,
          textAlign: "center",
          whiteSpace: "pre-line",
          fontFeatureSettings: '"lnum" 1',
          textShadow: "0 3px 28px rgba(40,30,20,0.55), 0 0 3px rgba(40,30,20,0.55)",
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

export const BlackFridayLift: React.FC<BlackFridayProps> = ({ discount, audio, lineTimes, spillStart }) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const t = frame / fps;

  const open = interpolate(t, [0.8, 3.0], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.inOut(Easing.cubic),
  });
  // Slow push-in towards the lift for the whole reel
  const push = interpolate(t, [0, durationInFrames / fps], [1, 1.05]);
  const band = interpolate(t, [1.4, 3.0], [0.35, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });

  return (
    <AbsoluteFill style={{ backgroundColor: "#EDE6DB" }}>
      <AbsoluteFill style={{ transform: `scale(${push})`, transformOrigin: "50% 46%" }}>
        <Img src={staticFile("bf/frame-B.png")} style={{ width: 1080, height: 1920 }} />
        {/* Doorway: the packed interior with the two door leaves sliding over it */}
        <div
          style={{
            position: "absolute",
            left: DOOR.left,
            top: DOOR.top,
            width: DOOR.right - DOOR.left,
            height: DOOR.bottom - DOOR.top,
            overflow: "hidden",
          }}
        >
          <Img
            src={staticFile("bf/frame-B.png")}
            style={{
              position: "absolute",
              left: -DOOR.left,
              top: -DOOR.top,
              width: 1080,
              height: 1920,
            }}
          />
          <DoorLeaf side="left" open={open} />
          <DoorLeaf side="right" open={open} />
        </div>
        <FallingBags start={spillStart} />
      </AbsoluteFill>
      <Line text={"BLACK\nFRIDAY"} from={0} to={lineTimes[0]} band={band} />
      <Line text={`UP TO\n${discount}% OFF`} from={lineTimes[0]} to={lineTimes[1]} band={band} />
      <Line text={"EARLY\nACCESS"} from={lineTimes[1]} to={durationInFrames / fps + 1} band={band} />
      {audio ? (
        <Audio
          src={staticFile(audio)}
          volume={(f) =>
            interpolate(f, [durationInFrames - 12, durationInFrames], [1, 0], {
              extrapolateLeft: "clamp",
              extrapolateRight: "clamp",
            })
          }
        />
      ) : null}
    </AbsoluteFill>
  );
};
