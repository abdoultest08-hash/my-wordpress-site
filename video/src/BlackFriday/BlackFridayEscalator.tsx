import { AbsoluteFill, Audio, interpolate, OffthreadVideo, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { Grain } from "./BlackFridayDark";
import { Line } from "./BlackFridayLift";

// Escalator reel: top-down London Underground escalator, steps gliding up and HLC packages riding
// into view. The footage itself is rendered frame by frame from one still (tools/escalator.py).

export type BlackFridayEscalatorProps = {
  audio: string | null;
  showText: boolean;
  textFrom: number;
  src?: string;
  // text position (px from centre) and a faint handheld sway for phone-shot realism
  textY?: number;
  sway?: boolean;
};

export const BlackFridayEscalator: React.FC<BlackFridayEscalatorProps> = ({
  audio,
  showText,
  textFrom,
  src = "bfe/escalator.mp4",
  textY = -610,
  sway = false,
}) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const t = frame / fps;
  const sx = sway ? Math.sin(t * 0.9) * 2.2 + Math.sin(t * 2.1) * 0.7 : 0;
  const sy = sway ? Math.cos(t * 0.7) * 1.8 + Math.sin(t * 1.7) * 0.5 : 0;
  const rot = sway ? Math.sin(t * 0.5) * 0.12 : 0;
  return (
    <AbsoluteFill style={{ backgroundColor: "#111" }}>
      <AbsoluteFill style={{ transform: `translate(${sx}px, ${sy}px) rotate(${rot}deg) scale(${sway ? 1.02 : 1})` }}>
        <OffthreadVideo src={staticFile(src)} muted style={{ width: "100%", height: "100%" }} />
      </AbsoluteFill>
      <AbsoluteFill
        style={{ background: "radial-gradient(ellipse 80% 70% at 50% 50%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.38) 100%)" }}
      />
      <Grain />
      {showText ? (
        <Line text={"BLACK FRIDAY\nSALE"} from={textFrom} to={durationInFrames / fps + 1} band={0.85} size={124} offsetY={textY} />
      ) : null}
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
