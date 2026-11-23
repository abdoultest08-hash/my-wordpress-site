import { AbsoluteFill, Audio, Easing, Img, interpolate, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { Grain } from "./BlackFridayDark";
import { Line } from "./BlackFridayLift";

// Mural reel: one high-resolution still (the logo painted on brick, packages on the ground).
// The camera starts tight on the painted strokes and pulls back in one smooth move. The image
// is laid out at its zoomed size every frame, so the browser samples the full-res source.

export type BlackFridayMuralProps = {
  audio: string | null;
  lineTimes: [number, number];
  zoom: [number, number]; // seconds: zoom-out start and end
};

const ZMAX = 2.6; // close-up magnification at the start
const FOCUS = { x: 0.73, y: 0.45 }; // painted figure + drips, as a fraction of the image

export const BlackFridayMural: React.FC<BlackFridayMuralProps> = ({ audio, lineTimes, zoom }) => {
  const frame = useCurrentFrame();
  const { fps, width, height, durationInFrames } = useVideoConfig();
  const t = frame / fps;

  const p = interpolate(t, zoom, [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
    easing: Easing.inOut(Easing.cubic),
  });
  // exponential zoom feels evenly paced; a whisper of extra drift before and after
  const drift = interpolate(t, [0, durationInFrames / fps], [0, 1]);
  const scale = Math.pow(ZMAX, 1 - p) * (1 + 0.025 * (1 - drift));
  const w = width * scale;
  const h = height * scale;
  // the focus point travels from screen centre to its natural place in the full frame
  const sx = interpolate(p, [0, 1], [width / 2, FOCUS.x * width]);
  const sy = interpolate(p, [0, 1], [height / 2, FOCUS.y * height]);
  const left = sx - FOCUS.x * w;
  const top = sy - FOCUS.y * h;
  // faint handheld sway
  const hx = Math.sin(t * 1.1) * 1.2 + Math.sin(t * 2.3) * 0.4;
  const hy = Math.cos(t * 0.9) * 0.9;

  return (
    <AbsoluteFill style={{ backgroundColor: "#1a1714", overflow: "hidden" }}>
      <Img
        src={staticFile("bfm/mural.png")}
        style={{ position: "absolute", left: Math.min(0, Math.max(width - w, left)) + hx, top: Math.min(0, Math.max(height - h, top)) + hy, width: w, height: h }}
      />
      <AbsoluteFill
        style={{ background: "radial-gradient(ellipse 78% 68% at 50% 45%, rgba(0,0,0,0) 58%, rgba(20,14,8,0.32) 100%)" }}
      />
      <Grain />
      <Line text={"BLACK FRIDAY\nSALE"} from={lineTimes[0]} to={lineTimes[1]} band={0.7} offsetY={150} size={128} />
      <Line text={"AVAILABLE\nNOW"} from={lineTimes[1]} to={durationInFrames / fps + 1} band={0.7} offsetY={150} size={128} />
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
