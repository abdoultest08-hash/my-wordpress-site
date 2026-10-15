import { useMemo } from "react";
import {
  AbsoluteFill,
  Audio,
  Img,
  interpolate,
  OffthreadVideo,
  Sequence,
  spring,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { brand } from "../brand";
import { Graphic, GraphicView, zoomAt } from "./Graphics";

export type Word = { text: string; start: number; end: number }; // seconds in source.mp4
export type Segment = { from: number; to: number }; // kept ranges in source.mp4
export type Broll = { src: string; at: number; dur: number }; // seconds on the OUTPUT timeline

export type TalkingShortProps = {
  src: string; // inside public/, e.g. "work/btl-mortgages/01/source.mp4"
  segments: Segment[];
  words: Word[];
  hook?: string;
  highlight?: string[]; // caption words to colour orange, e.g. ["£150k", "commercial"]
  broll?: Broll[];
  graphics?: Graphic[];
  music?: string | null;
  musicVolume?: number;
  endCard?: boolean;
  endCardText?: string;
};

export const END_CARD_SECONDS = 2;
const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;

export const speechSeconds = (segments: Segment[]) =>
  segments.reduce((a, s) => a + (s.to - s.from), 0);

export const totalSeconds = (p: TalkingShortProps) =>
  speechSeconds(p.segments) + (p.endCard ? END_CARD_SECONDS : 0);

// Move word timings from source.mp4 onto the output timeline (after pauses are cut out)
const mapWords = (words: Word[], segments: Segment[]): Word[] => {
  const out: Word[] = [];
  let offset = 0;
  for (const s of segments) {
    for (const w of words) {
      if (w.start >= s.from - 0.02 && w.start < s.to) {
        out.push({
          text: w.text,
          start: offset + Math.max(w.start, s.from) - s.from,
          end: offset + Math.min(w.end, s.to) - s.from,
        });
      }
    }
    offset += s.to - s.from;
  }
  return out;
};

type Page = { words: Word[]; start: number; end: number };

// Group words into short caption pages: max N words, break on pauses and punctuation
const toPages = (words: Word[], max: number): Page[] => {
  const pages: Page[] = [];
  let cur: Word[] = [];
  words.forEach((w, i) => {
    const prev = words[i - 1];
    const brk =
      cur.length >= max || (prev && (w.start - prev.end > 0.3 || /[.?!,]$/.test(prev.text)));
    if (cur.length && brk) {
      pages.push({ words: cur, start: cur[0].start, end: w.start });
      cur = [];
    }
    cur.push(w);
  });
  if (cur.length) pages.push({ words: cur, start: cur[0].start, end: cur[cur.length - 1].end + 0.4 });
  return pages;
};

const norm = (t: string) => t.toLowerCase().replace(/[^a-z0-9£%']/g, "");

const Captions: React.FC<{ words: Word[]; highlight: string[] }> = ({ words, highlight }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const t = frame / fps;
  const pages = useMemo(() => toPages(words, brand.wordsPerPage), [words]);
  const page = pages.find((p) => t >= p.start && t < p.end);
  if (!page) return null;
  const pop = spring({
    frame: frame - Math.round(page.start * fps),
    fps,
    durationInFrames: 6,
    config: { damping: 200 },
  });
  let active = -1;
  if (brand.karaoke) {
    page.words.forEach((w, i) => {
      if (w.start <= t) active = i;
    });
  }
  const keys = new Set(highlight.map(norm));
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          position: "absolute",
          top: brand.captionTop,
          width: brand.captionWidth,
          textAlign: "center",
          textWrap: "balance",
          transform: `scale(${0.9 + 0.1 * pop})`,
          fontFamily: brand.captionFont,
          fontWeight: brand.captionWeight,
          fontSize: brand.captionSize,
          lineHeight: 1.12,
          color: brand.captionColour,
          textTransform: brand.uppercase ? "uppercase" : "none",
          WebkitTextStroke: brand.captionStroke ? `${brand.captionStroke}px ${brand.ink}` : undefined,
          paintOrder: "stroke fill",
          textShadow: "0 2px 4px rgba(0,0,0,0.6), 0 4px 16px rgba(0,0,0,0.45)",
        }}
      >
        {page.words.map((w, i) => (
          <span key={i} style={{ color: i === active || keys.has(norm(w.text)) ? brand.accent : undefined }}>
            {w.text}
            {i < page.words.length - 1 ? " " : ""}
          </span>
        ))}
      </div>
    </AbsoluteFill>
  );
};

const Hook: React.FC<{ text: string }> = ({ text }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const endF = Math.round(brand.hookSeconds * fps);
  const opacity = interpolate(frame, [0, 4, endF, endF + 8], [0, 1, 1, 0], clamp);
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          position: "absolute",
          top: brand.hookTop,
          maxWidth: 940,
          padding: "18px 28px",
          borderRadius: 14,
          background: brand.hookBg,
          color: brand.hookColour,
          fontFamily: brand.hookFont,
          fontWeight: 900,
          fontSize: brand.hookSize,
          lineHeight: 1.05,
          textAlign: "center",
          textTransform: "uppercase",
          opacity,
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

const BrollView: React.FC<{ src: string; durationInFrames: number }> = ({ src, durationInFrames }) => {
  const frame = useCurrentFrame();
  const zoom = interpolate(frame, [0, durationInFrames], [1, 1.06], clamp);
  const style = { width: "100%", height: "100%", objectFit: "cover" } as const;
  return (
    <AbsoluteFill style={{ transform: `scale(${zoom})` }}>
      {/\.(jpe?g|png|webp)$/i.test(src) ? (
        <Img src={staticFile(src)} style={style} />
      ) : (
        <OffthreadVideo src={staticFile(src)} muted style={style} />
      )}
    </AbsoluteFill>
  );
};

const EndCard: React.FC<{ text: string }> = ({ text }) => {
  const frame = useCurrentFrame();
  const opacity = interpolate(frame, [0, 8], [0, 1], clamp);
  return (
    <AbsoluteFill
      style={{ background: brand.endBg, justifyContent: "center", alignItems: "center", gap: 40, opacity }}
    >
      {brand.logo ? <Img src={staticFile(brand.logo)} style={{ width: 420 }} /> : null}
      <div
        style={{
          fontFamily: brand.bodyFont,
          fontWeight: 800,
          fontSize: 64,
          color: brand.ink,
          textAlign: "center",
          textWrap: "balance",
          maxWidth: 860,
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

export const TalkingShort: React.FC<TalkingShortProps> = (p) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const f = (s: number) => Math.round(s * fps);
  const words = useMemo(() => mapWords(p.words, p.segments), [p.words, p.segments]);
  const speechEnd = speechSeconds(p.segments);
  const graphics = p.graphics ?? [];
  const zoom = zoomAt(graphics, frame / fps);

  let offset = 0;
  const clips = p.segments.map((s, i) => {
    const from = f(offset);
    offset += s.to - s.from;
    const dur = f(offset) - from;
    if (dur <= 0) return null;
    return (
      <Sequence key={i} from={from} durationInFrames={dur}>
        <OffthreadVideo
          src={staticFile(p.src)}
          trimBefore={f(s.from)}
          style={{ width: "100%", height: "100%", objectFit: "cover" }}
        />
      </Sequence>
    );
  });

  return (
    <AbsoluteFill style={{ backgroundColor: "black" }}>
      {/* Punch-ins centre slightly above middle so the face stays in frame */}
      <AbsoluteFill style={{ transform: `scale(${zoom})`, transformOrigin: "50% 40%" }}>{clips}</AbsoluteFill>
      {(p.broll ?? []).map((b, i) => (
        <Sequence key={`b${i}`} from={f(b.at)} durationInFrames={f(b.dur)}>
          <BrollView src={b.src} durationInFrames={f(b.dur)} />
        </Sequence>
      ))}
      {graphics.map((g, i) =>
        g.type === "zoom" ? null : (
          <Sequence key={`g${i}`} from={f(g.at)} durationInFrames={f(g.dur)}>
            <GraphicView g={g} durationInFrames={f(g.dur)} />
          </Sequence>
        ),
      )}
      <Sequence durationInFrames={f(speechEnd)}>
        <Captions words={words} highlight={p.highlight ?? []} />
      </Sequence>
      {p.hook ? (
        <Sequence durationInFrames={f(brand.hookSeconds) + 10}>
          <Hook text={p.hook} />
        </Sequence>
      ) : null}
      {p.endCard ? (
        <Sequence from={f(speechEnd)} durationInFrames={f(END_CARD_SECONDS)}>
          <EndCard text={p.endCardText ?? brand.endCardText} />
        </Sequence>
      ) : null}
      {p.music ? (
        <Audio
          src={staticFile(p.music)}
          loop
          volume={(fr) =>
            (p.musicVolume ?? 0.08) *
            interpolate(fr, [durationInFrames - 20, durationInFrames], [1, 0], clamp)
          }
        />
      ) : null}
    </AbsoluteFill>
  );
};
