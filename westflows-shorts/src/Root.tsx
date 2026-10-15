import { Composition } from "remotion";
import "./brand";
import { TalkingShort, TalkingShortProps, totalSeconds } from "./TalkingShort/TalkingShort";

const example: TalkingShortProps = {
  src: "work/example/01/source.mp4",
  segments: [{ from: 0, to: 5 }],
  words: [],
  hook: "Example hook",
  endCard: true,
};

export const RemotionRoot: React.FC = () => (
  <Composition
    id="TalkingShort"
    component={TalkingShort}
    fps={30}
    width={1080}
    height={1920}
    durationInFrames={300}
    defaultProps={example}
    calculateMetadata={({ props }) => ({
      durationInFrames: Math.ceil(totalSeconds(props) * 30),
    })}
  />
);
