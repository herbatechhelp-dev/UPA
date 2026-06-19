<script setup>
defineProps({
  show: { type: Boolean, default: false },
  message: { type: String, default: 'Memuat...' },
  uploadProgress: { type: Number, default: 0 },
  isUpload: { type: Boolean, default: false },
});
</script>

<template>
  <Transition name="loader-fade">
    <div
      v-if="show"
      class="islamic-loader-overlay"
      role="status"
      aria-label="Memuat"
    >
      <!-- Geometric Background Pattern -->
      <div class="geometric-bg"></div>

      <!-- Center Content -->
      <div class="loader-center">

        <!-- Animated Islamic Star -->
        <div class="star-container">
          <!-- Outer Ring -->
          <div class="outer-ring">
            <div class="outer-ring-dot" v-for="n in 8" :key="n" :style="`--i: ${n}`"></div>
          </div>

          <!-- Star of Rub el Hizb (8-pointed star) -->
          <svg
            class="islamic-star"
            viewBox="0 0 100 100"
            xmlns="http://www.w3.org/2000/svg"
          >
            <!-- 8-Pointed Star (Two overlapping squares) -->
            <polygon
              points="50,5 61,39 95,50 61,61 50,95 39,61 5,50 39,39"
              fill="none"
              stroke="#FBBF24"
              stroke-width="2"
              class="star-outline"
            />
            <polygon
              points="50,5 61,39 95,50 61,61 50,95 39,61 5,50 39,39"
              fill="#FBBF2415"
              class="star-fill"
            />
            <!-- Inner square rotated 22.5 degrees -->
            <polygon
              points="50,15 68,32 85,50 68,68 50,85 32,68 15,50 32,32"
              fill="none"
              stroke="#FBBF2460"
              stroke-width="1"
              transform="rotate(22.5, 50, 50)"
              class="star-inner"
            />
            <!-- Center Circle -->
            <circle cx="50" cy="50" r="10" fill="none" stroke="#FBBF24" stroke-width="2" class="star-center"/>
            <circle cx="50" cy="50" r="5" fill="#FBBF24" class="star-dot"/>
          </svg>

          <!-- Crescent Moon -->
          <div class="crescent-orbit">
            <svg class="crescent" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M20 5 A15 15 0 1 1 5 20 A10 10 0 1 0 20 5 Z"
                fill="#FBBF24"
              />
            </svg>
          </div>
        </div>

        <!-- Arabic "Sabar" text + loading message -->
        <div class="loader-text-block">
          <span class="arabic-text">بِسْمِ اللّٰهِ</span>
          <span class="loader-message">{{ message }}</span>

          <!-- Upload progress bar -->
          <div v-if="isUpload" class="progress-bar-wrapper">
            <div class="progress-bar-track">
              <div
                class="progress-bar-fill"
                :style="`width: ${uploadProgress}%`"
              ></div>
            </div>
            <span class="progress-text">{{ uploadProgress }}%</span>
          </div>
        </div>

      </div>
    </div>
  </Transition>
</template>

<style scoped>
/* ── Overlay ─────────────────────────────────────────────── */
.islamic-loader-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(2, 44, 34, 0.92);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

/* ── Geometric tile background ───────────────────────────── */
.geometric-bg {
  position: absolute;
  inset: 0;
  opacity: 0.08;
  background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolygon points='40,4 51,28 76,40 51,52 40,76 29,52 4,40 29,28' fill='none' stroke='%23FBBF24' stroke-width='1.5'/%3E%3Ccircle cx='40' cy='40' r='10' fill='none' stroke='%23FBBF24' stroke-width='1'/%3E%3C/svg%3E");
  background-repeat: repeat;
  animation: bg-drift 20s linear infinite;
}
@keyframes bg-drift {
  from { background-position: 0 0; }
  to   { background-position: 80px 80px; }
}

/* ── Center block ─────────────────────────────────────────── */
.loader-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

/* ── Star container ───────────────────────────────────────── */
.star-container {
  position: relative;
  width: 140px;
  height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Main SVG star — slow spin */
.islamic-star {
  width: 80px;
  height: 80px;
  animation: star-spin 8s linear infinite;
  drop-shadow: 0 0 12px #FBBF24;
  filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));
}
.star-outline { animation: star-glow 2s ease-in-out infinite alternate; }
.star-fill    { animation: star-glow-fill 2s ease-in-out infinite alternate; }
.star-inner   { animation: star-spin-r 12s linear infinite; }
.star-center  { animation: star-glow 1.5s ease-in-out infinite alternate; }
.star-dot     { animation: dot-pulse 1.5s ease-in-out infinite; }

@keyframes star-spin   { to { transform: rotate(360deg); transform-origin: 50px 50px; } }
@keyframes star-spin-r { to { transform: rotate(-360deg); transform-origin: 50px 50px; } }
@keyframes star-glow {
  from { stroke: #FBBF24; filter: drop-shadow(0 0 4px #FBBF24); }
  to   { stroke: #FDE68A; filter: drop-shadow(0 0 12px #FDE68A); }
}
@keyframes star-glow-fill {
  from { fill: #FBBF2415; }
  to   { fill: #FBBF2430; }
}
@keyframes dot-pulse {
  0%, 100% { r: 5; opacity: 1; }
  50%       { r: 7; opacity: 0.7; }
}

/* ── Outer rotating dots ring ────────────────────────────── */
.outer-ring {
  position: absolute;
  inset: 0;
  animation: outer-ring-spin 4s linear infinite;
}
.outer-ring-dot {
  position: absolute;
  width: 6px;
  height: 6px;
  background: #FBBF24;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform-origin: 0 0;
  transform: rotate(calc(var(--i) * 45deg)) translate(60px, -3px);
  opacity: 0.6;
  animation: dot-blink 1s ease-in-out calc(var(--i) * 0.125s) infinite alternate;
}
@keyframes outer-ring-spin { to { transform: rotate(360deg); } }
@keyframes dot-blink {
  from { opacity: 0.2; transform: rotate(calc(var(--i) * 45deg)) translate(60px, -3px) scale(0.7); }
  to   { opacity: 1;   transform: rotate(calc(var(--i) * 45deg)) translate(60px, -3px) scale(1.2); }
}

/* ── Crescent orbiting the star ──────────────────────────── */
.crescent-orbit {
  position: absolute;
  inset: 0;
  animation: crescent-orbit-spin 5s linear infinite;
}
.crescent {
  position: absolute;
  width: 22px;
  height: 22px;
  top: 4px;
  left: 50%;
  transform: translateX(-50%);
  filter: drop-shadow(0 0 4px rgba(251, 191, 36, 0.8));
}
@keyframes crescent-orbit-spin { to { transform: rotate(-360deg); } }

/* ── Text block ───────────────────────────────────────────── */
.loader-text-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}
.arabic-text {
  font-size: 1.4rem;
  color: #FDE68A;
  letter-spacing: 0.05em;
  text-shadow: 0 0 12px rgba(251, 191, 36, 0.5);
  font-family: 'Amiri', 'Scheherazade New', serif;
}
.loader-message {
  font-size: 0.75rem;
  color: #A7F3D0;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  animation: text-pulse 2s ease-in-out infinite;
}
@keyframes text-pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.5; }
}

/* ── Upload progress bar ──────────────────────────────────── */
.progress-bar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  width: 200px;
}
.progress-bar-track {
  width: 100%;
  height: 6px;
  background: rgba(255,255,255,0.1);
  border-radius: 999px;
  overflow: hidden;
  border: 1px solid rgba(251,191,36,0.2);
}
.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #FBBF24, #FDE68A);
  border-radius: 999px;
  transition: width 0.3s ease;
  box-shadow: 0 0 8px rgba(251,191,36,0.6);
}
.progress-text {
  font-size: 0.7rem;
  color: #FDE68A;
  font-weight: 700;
}

/* ── Transition ───────────────────────────────────────────── */
.loader-fade-enter-active,
.loader-fade-leave-active {
  transition: opacity 0.35s ease;
}
.loader-fade-enter-from,
.loader-fade-leave-to {
  opacity: 0;
}
</style>
