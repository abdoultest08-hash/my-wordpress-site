import {
  AbsoluteFill,
  Audio,
  Img,
  interpolate,
  OffthreadVideo,
  Sequence,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";

export type Shot = {
  // File inside public/: a video ("graded/clip2.mp4") or a still photo ("photos/cj-tag.jpg")
  src: string;
  // Where to start inside the source clip, in seconds (ignored for photos)
  from: number;
  // Soft cross-dissolve from the previous shot instead of a hard cut
  fade?: boolean;
};

export type LookbookProps = {
  shots: Shot[];
  // Cut times in seconds; cuts[i] is where shots[i] starts, last entry is the end
  cuts: number[];
  audio: string;
  // Fade the picture out to this colour over the last frames
  fadeOutTo?: string;
};

// Length of a cross-dissolve, centred on the cut
const FADE_FRAMES = 8;
const FADE_OUT_FRAMES = 15;

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
  const opacity = shot.fade
    ? interpolate(frame, [0, FADE_FRAMES], [0, 1], { extrapolateRight: "clamp" })
    : 1;

  const style = { width: "100%", height: "100%", objectFit: "cover" } as const;

  return (
    <AbsoluteFill style={{ transform: `scale(${zoom})`, opacity }}>
      {/\.(jpe?g|png)$/i.test(shot.src) ? (
        <Img src={staticFile(shot.src)} style={style} />
      ) : (
        <OffthreadVideo
          src={staticFile(shot.src)}
          trimBefore={Math.max(0, Math.round(shot.from * fps) - (shot.fade ? FADE_FRAMES / 2 : 0))}
          muted
          style={style}
        />
      )}
    </AbsoluteFill>
  );
};

export const Lookbook: React.FC<LookbookProps> = ({ shots, cuts, audio, fadeOutTo }) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const toFrame = (s: number) => Math.round(s * fps);

  return (
    <AbsoluteFill style={{ backgroundColor: "black" }}>
      {shots.map((shot, i) => {
        // A fading shot starts half a dissolve early; every shot runs a little
        // past its cut so there is always a picture underneath the next fade.
        const start = toFrame(cuts[i]) - (shot.fade ? FADE_FRAMES / 2 : 0);
        const end = toFrame(cuts[i + 1]) + (i < shots.length - 1 ? FADE_FRAMES : 0);
        return (
          <Sequence key={i} from={start} durationInFrames={end - start}>
            <ShotView shot={shot} index={i} durationInFrames={end - start} />
          </Sequence>
        );
      })}
      {fadeOutTo ? (
        <AbsoluteFill
          style={{
            backgroundColor: fadeOutTo,
            opacity: interpolate(
              frame,
              [durationInFrames - FADE_OUT_FRAMES, durationInFrames - 1],
              [0, 1],
              { extrapolateLeft: "clamp", extrapolateRight: "clamp" },
            ),
          }}
        />
      ) : null}
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
