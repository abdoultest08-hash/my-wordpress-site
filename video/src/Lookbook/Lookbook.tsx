import {
  AbsoluteFill,
  Audio,
  interpolate,
  OffthreadVideo,
  Sequence,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";

export type Shot = {
  // File inside public/, e.g. "clips/clip2.mp4"
  src: string;
  // Where to start inside the source clip, in seconds
  from: number;
};

export type LookbookProps = {
  shots: Shot[];
  // Cut times in seconds; cuts[i] is where shots[i] starts, last entry is the end
  cuts: number[];
  audio: string;
};

const ShotView: React.FC<{ shot: Shot; index: number; durationInFrames: number }> = ({
  shot,
  index,
  durationInFrames,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  // Slow push-in, alternating in/out so consecutive shots feel different
  const zoom = interpolate(frame, [0, durationInFrames], index % 2 === 0 ? [1, 1.06] : [1.06, 1], {
    extrapolateRight: "clamp",
  });

  return (
    <AbsoluteFill style={{ transform: `scale(${zoom})` }}>
      <OffthreadVideo
        src={staticFile(shot.src)}
        trimBefore={Math.round(shot.from * fps)}
        muted
        style={{ width: "100%", height: "100%", objectFit: "cover" }}
      />
    </AbsoluteFill>
  );
};

export const Lookbook: React.FC<LookbookProps> = ({ shots, cuts, audio }) => {
  const { fps, durationInFrames } = useVideoConfig();
  const toFrame = (s: number) => Math.round(s * fps);

  return (
    <AbsoluteFill style={{ backgroundColor: "black" }}>
      {shots.map((shot, i) => {
        const start = toFrame(cuts[i]);
        const length = toFrame(cuts[i + 1]) - start;
        return (
          <Sequence key={i} from={start} durationInFrames={length}>
            <ShotView shot={shot} index={i} durationInFrames={length} />
          </Sequence>
        );
      })}
      <Audio
        src={staticFile(audio)}
        volume={(f) =>
          interpolate(f, [durationInFrames - 10, durationInFrames], [1, 0], {
            extrapolateLeft: "clamp",
            extrapolateRight: "clamp",
          })
        }
      />
    </AbsoluteFill>
  );
};
