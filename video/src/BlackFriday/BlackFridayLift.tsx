import { loadFont } from "@remotion/fonts";
import {
  AbsoluteFill,
  Audio,
  Easing,
  Img,
  interpolate,
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
  // Still of the open lift (what the doors reveal)
  frameB: string;
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

export const Line: React.FC<{ text: string; from: number; to: number; band: number }> = ({
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

export const BlackFridayLift: React.FC<BlackFridayProps> = ({ discount, audio, lineTimes, frameB }) => {
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
  // Once the doors are open the packed bags ease forward very slightly, slowly and smoothly
  const drift = interpolate(t, [2.4, durationInFrames / fps], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.inOut(Easing.sin),
  });
  const band = interpolate(t, [1.4, 3.0], [0.35, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });

  return (
    <AbsoluteFill style={{ backgroundColor: "#EDE6DB" }}>
      <AbsoluteFill style={{ transform: `scale(${push})`, transformOrigin: "50% 46%" }}>
        <Img src={staticFile(frameB)} style={{ width: 1080, height: 1920 }} />
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
            src={staticFile(frameB)}
            style={{
              position: "absolute",
              left: -DOOR.left,
              top: -DOOR.top,
              width: 1080,
              height: 1920,
              transform: `translateY(${-6 * drift}px) scale(${1 + 0.02 * drift})`,
              transformOrigin: `${DOOR.seam}px ${DOOR.bottom}px`,
            }}
          />
          <DoorLeaf side="left" open={open} />
          <DoorLeaf side="right" open={open} />
        </div>
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
