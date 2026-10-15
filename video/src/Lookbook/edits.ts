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

// Women's sofa edit, brown tracksuit + corset & jeans (12s). Both looks were shot on the same
// cream sofa set, so the edit alternates them as one story. Cuts sit on the beats of the
// "Womens creamy set" soundtrack (~54 BPM, a beat every 1.1s).
export const sofaEdit: LookbookProps = {
  audio: "audio/womens-creamy-12s.m4a",
  fadeOutTo: "white",
  cuts: [0, 1.11, 2.29, 3.42, 4.42, 5.67, 6.63, 7.71, 8.79, 9.94, 12.14],
  shots: [
    { src: "graded/CJ-41.mp4", from: 0.0 }, // corset: seated close, hand in hair (hook)
    { src: "graded/BRF-14.mp4", from: 3.5 }, // brown: push-in to face + chest logo
    { src: "graded/CJ-32.mp4", from: 5.5 }, // corset: rose embroidery detail
    { src: "graded/BRF-21.mp4", from: 22.0 }, // brown: chest logo embroidery close-up
    { src: "graded/CJ-42.mp4", from: 0.5 }, // corset: side profile, hand at hair
    { src: "graded/BRF-21.mp4", from: 3.0 }, // brown: sofa selfie, playful
    { src: "graded/CJ-40.mp4", from: 5.0 }, // corset: slide down the rose jeans
    { src: "graded/clip22.mp4", from: 5.6 }, // brown: laughing close-up
    { src: "graded/CJ-41.mp4", from: 4.3 }, // corset: stands up, full outfit
    { src: "graded/BRF-14.mp4", from: 0.0, fade: true }, // brown: seated smile, fades to white
  ],
};
