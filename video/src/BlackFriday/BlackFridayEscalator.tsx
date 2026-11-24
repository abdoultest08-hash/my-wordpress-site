import { AbsoluteFill, Audio, interpolate, OffthreadVideo, staticFile, useVideoConfig } from "remotion";
import { Grain } from "./BlackFridayDark";
import { Line } from "./BlackFridayLift";

// Escalator reel: top-down London Underground escalator, steps gliding up and HLC packages riding
// into view. The footage itself is rendered frame by frame from one still (tools/escalator.py).

export type BlackFridayEscalatorProps = {
  audio: string | null;
  showText: boolean;
  textFrom: number;
};

export const BlackFridayEscalator: React.FC<BlackFridayEscalatorProps> = ({ audio, showText, textFrom }) => {
  const { fps, durationInFrames } = useVideoConfig();
  return (
    <AbsoluteFill style={{ backgroundColor: "#111" }}>
      <OffthreadVideo src={staticFile("bfe/escalator.mp4")} muted style={{ width: "100%", height: "100%" }} />
      <AbsoluteFill
        style={{ background: "radial-gradient(ellipse 80% 70% at 50% 50%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.38) 100%)" }}
      />
      <Grain />
      {showText ? (
        <Line text={"BLACK FRIDAY\nSALE"} from={textFrom} to={durationInFrames / fps + 1} band={0.85} size={124} offsetY={-610} />
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
