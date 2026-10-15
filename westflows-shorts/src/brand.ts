import { loadFont } from "@remotion/fonts";
import { staticFile } from "remotion";

// Defaults. Overwrite from library/STYLE.md after reference analysis.
export const brand = {
  accent: "#F6A404", // amber orange measured from the reference captions (site brand is #FF8D48)
  ink: "#17181c",
  white: "#ffffff",
  // Captions match reference/: Montserrat ExtraBold caps, white, soft shadow, no outline,
  // 2-4 words per page, keywords (not every word) in orange, sitting low (~1500px)
  captionFont: "Montserrat",
  captionWeight: 800,
  captionSize: 56,
  captionTop: 1480,
  captionWidth: 760,
  captionColour: "#ffffff",
  captionStroke: 0,
  wordsPerPage: 4,
  uppercase: true,
  karaoke: false, // true = colour the word being spoken; false = colour only props.highlight words
  hookFont: "Montserrat",
  hookSize: 68,
  hookTop: 260,
  hookBg: "#F6A404",
  hookColour: "#17181c",
  hookSeconds: 2.2,
  bodyFont: "Inter",
  graphicFont: "Anton", // stat numbers and list titles (you liked these in the demo)
  graphicTop: 300, // stat cards and lists sit above the face, below the top UI
  labelTop: 960, // small tags sit just above the captions
  endBg: "#ffffff",
  logo: "", // e.g. "brand/logo.png" once added to public/brand/
  endCardText: "Free deal calculator: link in bio",
};

// CTA mapping from the handover: numbers / finance -> calculator, SA / landlord -> landlord page
export const ctaText = {
  calculator: "Free deal calculator: link in bio",
  landlord: "Landlords: see how it works. Link in bio",
} as const;

loadFont({ family: "Montserrat", url: staticFile("brand/fonts/Montserrat.ttf"), weight: "100 900" });
loadFont({ family: "Anton", url: staticFile("brand/fonts/Anton-Regular.ttf") });
loadFont({ family: "Inter", url: staticFile("brand/fonts/Inter.ttf"), weight: "100 900" });
