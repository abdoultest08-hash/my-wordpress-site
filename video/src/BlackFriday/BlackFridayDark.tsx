import {
  AbsoluteFill,
  Audio,
  Easing,
  Img,
  interpolate,
  OffthreadVideo,
  Sequence,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { Line } from "./BlackFridayLift";

// Dark Black Friday reel: AI clip 1 (hand presses the call button, camera pulls back), AI clip 2
// (doors open on the packed lift), then a calm hold on clip 2's last frame. Film grain and a
// vignette on top so it reads as filmed footage.

export type BlackFridayDarkProps = {
  discount: number;
  audio: string | null;
  lineTimes: [number, number];
  clipSeconds: [number, number];
};

const fill = { width: "100%", height: "100%", objectFit: "cover" } as const;

export const Grain: React.FC = () => {
  const frame = useCurrentFrame();
  return (
    <AbsoluteFill style={{ opacity: 0.09, mixBlendMode: "overlay", pointerEvents: "none" }}>
      <svg width="1080" height="1920">
        <filter id="grain">
          <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed={frame % 24} stitchTiles="stitch" />
          <feColorMatrix type="saturate" values="0" />
        </filter>
        <rect width="100%" height="100%" filter="url(#grain)" />
      </svg>
    </AbsoluteFill>
  );
};

const Hold: React.FC<{ durationInFrames: number }> = ({ durationInFrames }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const p = interpolate(frame, [0, durationInFrames], [0, 1], { easing: Easing.inOut(Easing.sin) });
  // very slow push-in plus a faint handheld sway
  const t = frame / fps;
  const sx = Math.sin(t * 1.3) * 1.6 + Math.sin(t * 2.9) * 0.6;
  const sy = Math.cos(t * 1.1) * 1.2;
  return (
    <AbsoluteFill style={{ transform: `translate(${sx}px, ${sy}px) scale(${1.01 + 0.035 * p})`, transformOrigin: "62% 52%" }}>
      <Img src={staticFile("bfd/hold.png")} style={fill} />
    </AbsoluteFill>
  );
};

export const BlackFridayDark: React.FC<BlackFridayDarkProps> = ({ discount, audio, lineTimes, clipSeconds }) => {
  const { fps, durationInFrames } = useVideoConfig();
  const c1 = Math.round(clipSeconds[0] * fps);
  const c2 = Math.round(clipSeconds[1] * fps);
  const holdFrom = c1 + c2;

  return (
    <AbsoluteFill style={{ backgroundColor: "#111" }}>
      <Sequence durationInFrames={c1}>
        <OffthreadVideo src={staticFile("bfd/clip1.mp4")} muted style={fill} />
      </Sequence>
      <Sequence from={c1} durationInFrames={c2}>
        <OffthreadVideo src={staticFile("bfd/clip2.mp4")} muted style={fill} />
      </Sequence>
      <Sequence from={holdFrom}>
        <Hold durationInFrames={durationInFrames - holdFrom} />
      </Sequence>
      {/* vignette */}
      <AbsoluteFill
        style={{ background: "radial-gradient(ellipse 75% 65% at 50% 48%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.45) 100%)" }}
      />
      <Grain />
      <Line text={"BLACK\nFRIDAY"} from={0} to={lineTimes[0]} band={0.6} />
      <Line text={`UP TO\n${discount}% OFF`} from={lineTimes[0]} to={lineTimes[1]} band={0.6} />
      <Line text={"EARLY\nACCESS"} from={lineTimes[1]} to={durationInFrames / fps + 1} band={0.6} />
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
