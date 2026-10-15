import {
  AbsoluteFill,
  Easing,
  interpolate,
  spring,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { brand } from "../brand";

// All times are seconds on the OUTPUT timeline (after pauses are cut out).
// "zoom" is not drawn here: TalkingShort applies it to the video layer.
export type Graphic =
  | {
      type: "stat"; // big number that counts up, e.g. £1,250 / 6.2%
      at: number;
      dur: number;
      value: number;
      prefix?: string;
      suffix?: string;
      decimals?: number;
      label?: string;
    }
  | {
      type: "list"; // ticklist, each item appears at its own time
      at: number;
      dur: number;
      title?: string;
      items: { text: string; at: number }[];
    }
  | { type: "label"; at: number; dur: number; text: string } // small tag above the captions
  | { type: "zoom"; at: number; dur: number; scale?: number }; // punch-in, max ~1.3

const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;

// Shared in/out: pop in over ~8 frames, fade out over the last 6
const useEnterExit = (durationInFrames: number) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 180 } });
  const exit = interpolate(frame, [durationInFrames - 6, durationInFrames], [1, 0], clamp);
  return { frame, fps, enter, exit };
};

const card = {
  background: brand.white,
  color: brand.ink,
  borderRadius: 28,
  boxShadow: "0 18px 50px rgba(0,0,0,0.35)",
} as const;

const formatNumber = (n: number, decimals: number) =>
  n.toLocaleString("en-GB", { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

const Stat: React.FC<Extract<Graphic, { type: "stat" }> & { durationInFrames: number }> = (g) => {
  const { frame, fps, enter, exit } = useEnterExit(g.durationInFrames);
  const count = interpolate(frame, [0, Math.round(0.5 * fps)], [0, g.value], {
    ...clamp,
    easing: Easing.out(Easing.cubic),
  });
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          ...card,
          position: "absolute",
          top: brand.graphicTop,
          padding: "26px 56px 30px",
          textAlign: "center",
          opacity: exit,
          transform: `scale(${0.6 + 0.4 * enter}) translateY(${(1 - enter) * 40}px)`,
        }}
      >
        <div style={{ fontFamily: brand.graphicFont, fontSize: 170, lineHeight: 1, color: brand.accent }}>
          {g.prefix ?? ""}
          {formatNumber(count, g.decimals ?? 0)}
          {g.suffix ?? ""}
        </div>
        {g.label ? (
          <div
            style={{
              fontFamily: brand.bodyFont,
              fontWeight: 800,
              fontSize: 44,
              marginTop: 8,
              textTransform: "uppercase",
              letterSpacing: 1,
            }}
          >
            {g.label}
          </div>
        ) : null}
      </div>
    </AbsoluteFill>
  );
};

const List: React.FC<Extract<Graphic, { type: "list" }> & { durationInFrames: number }> = (g) => {
  const { frame, fps, enter, exit } = useEnterExit(g.durationInFrames);
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          ...card,
          position: "absolute",
          top: brand.graphicTop,
          width: 820,
          padding: "34px 44px",
          opacity: exit,
          transform: `translateX(${(1 - enter) * -120}px)`,
        }}
      >
        {g.title ? (
          <div style={{ fontFamily: brand.graphicFont, fontSize: 64, textTransform: "uppercase", marginBottom: 14 }}>
            {g.title}
          </div>
        ) : null}
        {g.items.map((item, i) => {
          const local = frame - Math.round((item.at - g.at) * fps);
          if (local < 0) return null;
          const pop = spring({ frame: local, fps, config: { damping: 14, stiffness: 200 } });
          return (
            <div
              key={i}
              style={{
                display: "flex",
                alignItems: "center",
                gap: 22,
                fontFamily: brand.bodyFont,
                fontWeight: 800,
                fontSize: 48,
                lineHeight: 1.15,
                padding: "10px 0",
                opacity: pop,
                transform: `translateX(${(1 - pop) * 40}px)`,
              }}
            >
              <span
                style={{
                  flex: "0 0 56px",
                  height: 56,
                  borderRadius: 28,
                  background: brand.accent,
                  color: brand.ink,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  fontSize: 36,
                  transform: `scale(${pop})`,
                }}
              >
                ✓
              </span>
              {item.text}
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

const Label: React.FC<{ text: string; durationInFrames: number }> = ({ text, durationInFrames }) => {
  const { enter, exit } = useEnterExit(durationInFrames);
  return (
    <AbsoluteFill>
      <div
        style={{
          position: "absolute",
          top: brand.labelTop,
          left: 70,
          padding: "14px 28px",
          borderRadius: 12,
          background: brand.ink,
          color: brand.white,
          borderLeft: `10px solid ${brand.accent}`,
          fontFamily: brand.bodyFont,
          fontWeight: 800,
          fontSize: 46,
          textTransform: "uppercase",
          letterSpacing: 1,
          opacity: exit,
          transform: `translateX(${(1 - enter) * -500}px)`,
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

export const GraphicView: React.FC<{ g: Graphic; durationInFrames: number }> = ({ g, durationInFrames }) => {
  if (g.type === "stat") return <Stat {...g} durationInFrames={durationInFrames} />;
  if (g.type === "list") return <List {...g} durationInFrames={durationInFrames} />;
  if (g.type === "label") return <Label text={g.text} durationInFrames={durationInFrames} />;
  return null;
};

// Video-layer scale at output time t: fast punch-in, hold, ease back out
export const zoomAt = (graphics: Graphic[], t: number): number => {
  let z = 1;
  for (const g of graphics) {
    if (g.type !== "zoom") continue;
    const s = Math.min(g.scale ?? 1.15, 1.3);
    z = Math.max(
      z,
      interpolate(t, [g.at, g.at + 0.12, g.at + g.dur - 0.2, g.at + g.dur], [1, s, s, 1], {
        ...clamp,
        easing: Easing.out(Easing.quad),
      }),
    );
  }
  return z;
};
