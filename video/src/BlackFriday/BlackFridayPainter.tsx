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
import { Grain } from "./BlackFridayDark";
import { Line } from "./BlackFridayLift";

// Painter reel: AI clip (brush finishing the mural, camera pulls back), then a hold on the
// mural that keeps drifting outwards. The clip plays slightly zoomed in so the hold can
// continue the pull-back without showing an edge.

export type BlackFridayPainterProps = {
  audio: string | null;
  clipSeconds: number;
  lineTimes: [number, number];
};

const fill = { width: "100%", height: "100%", objectFit: "cover" } as const;
const ZOOM = 1.08;

const Hold: React.FC<{ durationInFrames: number }> = ({ durationInFrames }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const p = interpolate(frame, [0, durationInFrames], [0, 1], { easing: Easing.out(Easing.sin) });
  const t = frame / fps;
  const sx = Math.sin(t * 1.2) * 1.4;
  const sy = Math.cos(t * 0.9) * 1.0;
  return (
    <AbsoluteFill style={{ transform: `translate(${sx}px, ${sy}px) scale(${ZOOM - (ZOOM - 1) * p})`, transformOrigin: "50% 38%" }}>
      <Img src={staticFile("bfp/hold.png")} style={fill} />
    </AbsoluteFill>
  );
};

export const BlackFridayPainter: React.FC<BlackFridayPainterProps> = ({ audio, clipSeconds, lineTimes }) => {
  const { fps, durationInFrames } = useVideoConfig();
  const clip = Math.round(clipSeconds * fps);
  return (
    <AbsoluteFill style={{ backgroundColor: "#111" }}>
      <Sequence durationInFrames={clip}>
        <AbsoluteFill style={{ transform: `scale(${ZOOM})`, transformOrigin: "50% 38%" }}>
          <OffthreadVideo src={staticFile("bfp/clip.mp4")} muted style={fill} />
        </AbsoluteFill>
      </Sequence>
      <Sequence from={clip}>
        <Hold durationInFrames={durationInFrames - clip} />
      </Sequence>
      <AbsoluteFill
        style={{ background: "radial-gradient(ellipse 78% 68% at 50% 45%, rgba(0,0,0,0) 58%, rgba(20,14,8,0.35) 100%)" }}
      />
      <Grain />
      {/* lower third, clear of the mural */}
      <Line text={"BLACK FRIDAY\nSALE"} from={lineTimes[0]} to={lineTimes[1]} band={0.7} offsetY={470} size={128} />
      <Line text={"AVAILABLE\nNOW"} from={lineTimes[1]} to={durationInFrames / fps + 1} band={0.7} offsetY={470} size={128} />
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
