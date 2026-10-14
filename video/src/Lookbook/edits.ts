import type { LookbookProps } from "./Lookbook";

// Brown tracksuit, Edit A: fast cuts timed to the "Both sets" reference soundtrack (18s)
export const brownTracksuitA: LookbookProps = {
  audio: "audio/both-sets.m4a",
  fadeOutTo: "white",
  cuts: [
    0, 1.458, 2.333, 3.542, 4.583, 5.667, 6.792, 7.917, 9, 10.125, 11.125, 12.208, 13.333,
    14.333, 16.542, 18.125,
  ],
  shots: [
    { src: "graded/clip28.mp4", from: 8.0 }, // over-the-shoulder look (hook)
    { src: "graded/c2chestA.mp4", from: 0 }, // hoodie chest logo close-up
    { src: "graded/clip2.mp4", from: 10.0 }, // full body, hands in pockets
    { src: "graded/c28jogA.mp4", from: 0 }, // jogger logo close-up
    { src: "graded/clip22.mp4", from: 0.3 }, // lying on sofa, arm up
    { src: "graded/clip18.mp4", from: 12.0 }, // seated on sofa, wide
    { src: "graded/c18chest.mp4", from: 0 }, // seated, chest + face close-up
    { src: "graded/clip28.mp4", from: 2.0 }, // looking down at hoodie
    { src: "graded/clip2.mp4", from: 14.0 }, // closer walk-in
    { src: "graded/clip22.mp4", from: 5.6 }, // smile close-up
    { src: "graded/c28face.mp4", from: 0 }, // over-the-shoulder face close-up
    { src: "graded/clip18.mp4", from: 0.5 }, // seated, medium
    { src: "graded/c2jog.mp4", from: 0 }, // joggers close-up
    { src: "graded/clip28.mp4", from: 5.8 }, // turn to camera
    { src: "graded/clip28.mp4", from: 16.0 }, // final pose hold
  ],
};

// Brown tracksuit, Edit B: calmer cuts timed to the "Womens creamy set" reference soundtrack (15s)
export const brownTracksuitB: LookbookProps = {
  audio: "audio/womens-creamy.m4a",
  fadeOutTo: "white",
  cuts: [0, 2.292, 3.417, 5.667, 7.708, 8.792, 11.75, 13.292, 15],
  shots: [
    { src: "graded/clip2.mp4", from: 8.5 }, // walking towards camera
    { src: "graded/c2chestB.mp4", from: 0 }, // hoodie chest logo close-up
    { src: "graded/clip28.mp4", from: 1.0 }, // looking down at hoodie
    { src: "graded/clip18.mp4", from: 10.0 }, // seated on sofa
    { src: "graded/c28jogB.mp4", from: 0 }, // jogger logo close-up
    { src: "graded/clip22.mp4", from: 0.0 }, // lying on sofa, playful
    { src: "graded/clip22.mp4", from: 5.5 }, // smile close-up
    { src: "graded/clip28.mp4", from: 9.0 }, // over-the-shoulder final pose
  ],
};

// Brown + black tracksuit mix: alternates the two colours on the "Both sets" cuts (18s),
// with soft dissolves at the sofa moment, the face close-up and the closing turn
export const tracksuitMix: LookbookProps = {
  audio: "audio/both-sets.m4a",
  fadeOutTo: "white",
  cuts: [
    0, 1.458, 2.333, 3.542, 4.583, 5.667, 6.792, 7.917, 9, 10.125, 11.125, 12.208, 13.333,
    14.333, 16.542, 18.125,
  ],
  shots: [
    { src: "graded/clip28.mp4", from: 8.0 }, // brown: over-the-shoulder look (hook)
    { src: "graded/clip56.mp4", from: 7.5 }, // black: full body, hand at hair
    { src: "graded/c2chestA.mp4", from: 0 }, // brown: hoodie chest logo close-up
    { src: "graded/clip53.mp4", from: 1.0 }, // black: jogger logo detail
    { src: "graded/clip22.mp4", from: 0.3 }, // brown: lying on sofa, arm up
    { src: "graded/c56chest.mp4", from: 0 }, // black: hoodie chest logo close-up
    { src: "graded/clip18.mp4", from: 12.0, fade: true }, // brown: seated on sofa
    { src: "graded/clip61.mp4", from: 0.0 }, // black: medium shot
    { src: "graded/clip28.mp4", from: 2.0 }, // brown: looking down at hoodie
    { src: "graded/clip53.mp4", from: 5.0 }, // black: jogger + hand detail
    { src: "graded/clip22.mp4", from: 5.6 }, // brown: smile close-up
    { src: "graded/c61face.mp4", from: 0.2, fade: true }, // black: face + logo close-up
    { src: "graded/c2jog.mp4", from: 0 }, // brown: joggers close-up
    { src: "graded/clip28.mp4", from: 5.8, fade: true }, // brown: turn to camera
    { src: "graded/clip56.mp4", from: 12.3 }, // black: final pose, fades to white
  ],
};
