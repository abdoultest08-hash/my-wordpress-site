import { loadFont } from "@remotion/fonts";
import { staticFile } from "remotion";

// Defaults. Overwrite from library/STYLE.md after reference analysis.
export const brand = {
  accent: "#FF8D48",
  ink: "#17181c",
  white: "#ffffff",
  captionFont: "Anton",
  captionSize: 96,
  captionTop: 1130, // px from top, inside the safe zone
  captionColour: "#ffffff",
  captionStroke: 10,
  wordsPerPage: 3,
  uppercase: true,
  hookFont: "Anton",
  hookSize: 78,
  hookTop: 260,
  hookBg: "#FF8D48",
  hookColour: "#17181c",
  hookSeconds: 2.2,
  bodyFont: "Inter",
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

loadFont({ family: "Anton", url: staticFile("brand/fonts/Anton-Regular.ttf") });
loadFont({ family: "Inter", url: staticFile("brand/fonts/Inter.ttf"), weight: "100 900" });
