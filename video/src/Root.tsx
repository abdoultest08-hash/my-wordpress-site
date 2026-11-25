import { Composition, Folder } from "remotion";
import { HelloWorld } from "./HelloWorld";
import { Logo } from "./HelloWorld/Logo";
import { Title } from "./HelloWorld/Title";
import { BlackFridayDark } from "./BlackFriday/BlackFridayDark";
import { BlackFridayEscalator } from "./BlackFriday/BlackFridayEscalator";
import { ByDesign } from "./BlackFriday/ByDesign";
import { BlackFridayLift } from "./BlackFriday/BlackFridayLift";
import { BlackFridayMural } from "./BlackFriday/BlackFridayMural";
import { BlackFridayPainter } from "./BlackFriday/BlackFridayPainter";
import { Lookbook } from "./Lookbook/Lookbook";
import {
  brownTracksuitA,
  brownTracksuitB,
  sofaEdit,
  sofaRemixGunna,
  studioRoseNere,
  tracksuitMix,
} from "./Lookbook/edits";

// Each <Composition> is an entry in the sidebar!

export const RemotionRoot: React.FC = () => {
  return (
    <>
      <Folder name="Elements">
        <Composition
          id="Logo"
          component={Logo}
          durationInFrames={150}
          fps={30}
          width={1920}
          height={1080}
          defaultProps={{
            logoColor1: "#91EAE4",
            logoColor2: "#86A8E7",
          }}
        />
        <Composition
          id="Title"
          component={Title}
          durationInFrames={115}
          fps={30}
          width={1920}
          height={1080}
          defaultProps={{
            titleText: "Welcome to Remotion",
            titleColor: "#000000",
          }}
        />
      </Folder>
      <Composition
        // You can take the "id" to render a video:
        // bunx remotion render HelloWorld
        id="HelloWorld"
        component={HelloWorld}
        durationInFrames={150}
        fps={30}
        width={1920}
        height={1080}
        // You can override these props for each render:
        // https://www.remotion.dev/docs/parametrized-rendering
        defaultProps={{
          titleText: "Welcome to Remotion",
          titleColor: "#000000",
        }}
      />

      <Folder name="Celeste">
        <Composition
          id="BrownTracksuitA"
          component={Lookbook}
          durationInFrames={Math.round(18.125 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={brownTracksuitA}
        />
        <Composition
          id="BrownTracksuitB"
          component={Lookbook}
          durationInFrames={15 * 30}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={brownTracksuitB}
        />
        <Composition
          id="TracksuitMix"
          component={Lookbook}
          durationInFrames={Math.round(18.125 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={tracksuitMix}
        />
        <Composition
          id="SofaEdit"
          component={Lookbook}
          durationInFrames={Math.round(12.14 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={sofaEdit}
        />
        <Composition
          id="SofaRemixGunna"
          component={Lookbook}
          durationInFrames={Math.round(12 * 1.173 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={sofaRemixGunna}
        />
        <Composition
          id="StudioRoseNere"
          component={Lookbook}
          durationInFrames={Math.round(26 * 0.5341 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={studioRoseNere}
        />
      </Folder>
      <Folder name="BlackFriday">
        <Composition
          id="BlackFridayLift"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: "audio/creamy-13s.m4a", lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B.png" }}
        />
        <Composition
          id="BlackFridayLiftSilent"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: null, lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B.png" }}
        />
        <Composition
          id="BlackFridayDark"
          component={BlackFridayDark}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: "audio/creamy-13s.m4a", lineTimes: [3.32, 7.73] as [number, number], clipSeconds: [31 / 6, 31 / 6] as [number, number] }}
        />
        <Composition
          id="BlackFridayDarkSilent"
          component={BlackFridayDark}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: null, lineTimes: [3.32, 7.73] as [number, number], clipSeconds: [31 / 6, 31 / 6] as [number, number] }}
        />
        <Composition
          id="BlackFridayLiftMixed"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: "audio/creamy-13s.m4a", lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B-mixed.png" }}
        />
        <Composition
          id="BlackFridayLiftMixedSilent"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: null, lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B-mixed.png" }}
        />
        <Composition
          id="BlackFridayPainter"
          component={BlackFridayPainter}
          durationInFrames={Math.round(12.14 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", clipSeconds: 6.6, lineTimes: [6.63, 9.94] as [number, number] }}
        />
        <Composition
          id="BlackFridayPainterSilent"
          component={BlackFridayPainter}
          durationInFrames={Math.round(12.14 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, clipSeconds: 6.6, lineTimes: [6.63, 9.94] as [number, number] }}
        />
        <Composition
          id="BlackFridayMural"
          component={BlackFridayMural}
          durationInFrames={Math.round(12.14 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", lineTimes: [6.63, 9.94] as [number, number], zoom: [1.0, 8.0] as [number, number] }}
        />
        <Composition
          id="BlackFridayMuralSilent"
          component={BlackFridayMural}
          durationInFrames={Math.round(12.14 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, lineTimes: [6.63, 9.94] as [number, number], zoom: [1.0, 8.0] as [number, number] }}
        />
        <Composition
          id="BlackFridayLiftMixedNoText"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: "audio/creamy-13s.m4a", lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B-mixed.png", showText: false }}
        />
        <Composition
          id="BlackFridayLiftMixedNoTextSilent"
          component={BlackFridayLift}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ discount: 30, audio: null, lineTimes: [3.32, 7.73] as [number, number], frameB: "bf/frame-B-mixed.png", showText: false }}
        />
        <Composition
          id="BlackFridayEscalatorText"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", showText: true, textFrom: 6.63 }}
        />
        <Composition
          id="BlackFridayEscalatorTextSilent"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, showText: true, textFrom: 6.63 }}
        />
        <Composition
          id="BlackFridayEscalatorNoText"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", showText: false, textFrom: 6.63 }}
        />
        <Composition
          id="BlackFridayEscalatorNoTextSilent"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, showText: false, textFrom: 6.63 }}
        />
        <Composition
          id="BlackFridayTubeText"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", showText: true, textFrom: 6.63, src: "bfe/tube.mp4", textY: -150, sway: true }}
        />
        <Composition
          id="BlackFridayTubeTextSilent"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, showText: true, textFrom: 6.63, src: "bfe/tube.mp4", textY: -150, sway: true }}
        />
        <Composition
          id="BlackFridayTubeNoText"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: "audio/creamy-13s.m4a", showText: false, textFrom: 6.63, src: "bfe/tube.mp4", textY: -150, sway: true }}
        />
        <Composition
          id="BlackFridayTubeNoTextSilent"
          component={BlackFridayEscalator}
          durationInFrames={364}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ audio: null, showText: false, textFrom: 6.63, src: "bfe/tube.mp4", textY: -150, sway: true }}
        />
        <Composition
          id="ByDesignBrown"
          component={ByDesign}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ sketchFront: "bd/sk-brown-front.png", sketchBack: "bd/sk-brown-back.png", pin: "bd/pin-brown.jpg", detail: "bd/det-brown.jpg", swatches: ["bd/sw-brown.jpg", "bd/sw-black.jpg", "bd/sw-mocha.jpg"] as [string, string, string], note: ["SOFT FORM", "FLEECE", "TRACKSUIT"] as [string, string, string], audio: "audio/creamy-13s.m4a" }}
        />
        <Composition
          id="ByDesignBrownSilent"
          component={ByDesign}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ sketchFront: "bd/sk-brown-front.png", sketchBack: "bd/sk-brown-back.png", pin: "bd/pin-brown.jpg", detail: "bd/det-brown.jpg", swatches: ["bd/sw-brown.jpg", "bd/sw-black.jpg", "bd/sw-mocha.jpg"] as [string, string, string], note: ["SOFT FORM", "FLEECE", "TRACKSUIT"] as [string, string, string], audio: null }}
        />
        <Composition
          id="ByDesignCorset"
          component={ByDesign}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ sketchFront: "bd/sk-corset-front.png", sketchBack: "bd/sk-corset-back.png", pin: "bd/pin-corset.jpg", detail: "bd/det-corset.jpg", swatches: ["bd/sw-corset.jpg", "bd/sw-denim.jpg", "bd/sw-floral.jpg"] as [string, string, string], note: ["FLORAL", "CORSET", "& ROSE DENIM"] as [string, string, string], audio: "audio/creamy-13s.m4a" }}
        />
        <Composition
          id="ByDesignCorsetSilent"
          component={ByDesign}
          durationInFrames={Math.round(13.25 * 30)}
          fps={30}
          width={1080}
          height={1920}
          defaultProps={{ sketchFront: "bd/sk-corset-front.png", sketchBack: "bd/sk-corset-back.png", pin: "bd/pin-corset.jpg", detail: "bd/det-corset.jpg", swatches: ["bd/sw-corset.jpg", "bd/sw-denim.jpg", "bd/sw-floral.jpg"] as [string, string, string], note: ["FLORAL", "CORSET", "& ROSE DENIM"] as [string, string, string], audio: null }}
        />
      </Folder>
    </>
  );
};
