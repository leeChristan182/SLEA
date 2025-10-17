@extends('layouts.app')
@section('title', 'Performance Overview')

@section('content')
<div class="performance-page">
  <div class="container">
    @include('partials.sidebar')

    <main class="main-content">
      <!-- Score Card -->
      <section class="po-card po-score">
        <div class="po-medal"><i class="fa-regular fa-medal"></i></div>
        <div class="po-points" id="totalPoints">0</div>
        <div class="po-sub">Total Accumulated<br>Points</div>
      </section>

      <!-- Overall Progress -->
      <section class="po-card">
        <h3 class="po-title">Overall Progress</h3>
        <div class="po-progress">
          <div class="po-progress-fill" id="overallFill" style="width:0%"></div>
        </div>
        <div class="po-progress-legend">
          <span><strong id="earnedLegend">0</strong> Points Earned</span>
          <span><strong id="maxLegend">0</strong> Total Points</span>
        </div>
      </section>

      <!-- Category Progress -->
      <section class="po-card">
        <h3 class="po-title">Points Per Categories</h3>
        <div id="categoryList" class="po-category-list"></div>
      </section>

      <p class="po-note">
        Placeholder view. When backend is ready,
      </p>
    </main>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
  /* ——— Scoped to this page ——— */
  .performance-page .po-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 16px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, .06)
  }

  body.dark-mode .performance-page .po-card {
    background: #333;
    border-color: #555;
    color: #f1f1f1
  }

  .performance-page .po-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    max-width: 360px;
    margin: 0 auto 16px
  }

  .performance-page .po-medal {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #7b0000;
    font-size: 28px
  }

  body.dark-mode .performance-page .po-medal {
    background: #2b2b2b
  }

  .performance-page .po-points {
    font-size: 44px;
    font-weight: 800;
    line-height: 1;
    margin-top: 2px
  }

  .performance-page .po-sub {
    font-size: 12px;
    text-align: center;
    color: #6b7280
  }

  .performance-page .po-title {
    margin: 0 0 10px;
    color: #111827;
    font-weight: 700
  }

  body.dark-mode .performance-page .po-title {
    color: #f4f4f4
  }

  .performance-page .po-progress {
    height: 14px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden
  }

  body.dark-mode .performance-page .po-progress {
    background: #444
  }

  .performance-page .po-progress-fill {
    height: 100%;
    background: #22c55e;
    border-radius: 999px;
    transition: width .6s ease
  }

  .performance-page .po-progress-legend {
    display: flex;
    justify-content: space-between;
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280
  }

  .performance-page .po-category-list {
    display: flex;
    flex-direction: column;
    gap: 12px
  }

  .performance-page .po-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px
  }

  @media (max-width:720px) {
    .performance-page .po-row {
      grid-template-columns: 1fr
    }
  }

  .performance-page .po-cat {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 12px
  }

  body.dark-mode .performance-page .po-cat {
    background: #2a2a2a;
    border-color: #444
  }

  .performance-page .po-cat-title {
    font-size: 12px;
    color: #374151;
    margin-bottom: 8px
  }

  body.dark-mode .performance-page .po-cat-title {
    color: #d1d5db
  }

  .performance-page .po-cat-bar {
    height: 10px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden
  }

  body.dark-mode .performance-page .po-cat-bar {
    background: #444
  }

  .performance-page .po-cat-fill {
    height: 100%;
    background: #22c55e;
    border-radius: 999px;
    transition: width .5s ease
  }

  .performance-page .po-cat-legend {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    margin-top: 6px;
    color: #6b7280
  }

  .performance-page .po-note {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px
  }
</style>

<script>
  /**
   * PLACEHOLDER: uses window.__PERF__ if present; else fallback defaults.
   * No PHP variables are referenced.
   */
  const perfData = (window.__PERF__) || {
    totals: {
      earned: 45,
      max: 90
    },
    categories: [{
        key: 'leadership',
        label: 'I. Leadership Excellence',
        earned: 10,
        max: 20
      },
      {
        key: 'academic',
        label: 'II. Academic Excellence',
        earned: 10,
        max: 20
      },
      {
        key: 'awards',
        label: 'III. Awards/Recognition',
        earned: 10,
        max: 20
      },
      {
        key: 'community',
        label: 'IV. Community Involvement',
        earned: 10,
        max: 20
      },
      {
        key: 'conduct',
        label: 'V. Good Conduct',
        earned: 5,
        max: 10
      },
    ]
  };

  (function render(data) {
    // Totals
    const totalPts = document.getElementById('totalPoints');
    const overallFill = document.getElementById('overallFill');
    const earnedLegend = document.getElementById('earnedLegend');
    const maxLegend = document.getElementById('maxLegend');

    totalPts.textContent = data.totals.earned ?? 0;
    earnedLegend.textContent = data.totals.earned ?? 0;
    maxLegend.textContent = data.totals.max ?? 0;

    const pct = (data.totals.max > 0) ? (data.totals.earned / data.totals.max) * 100 : 0;
    requestAnimationFrame(() => overallFill.style.width = Math.min(100, pct).toFixed(2) + '%');

    // Categories
    const holder = document.getElementById('categoryList');
    holder.innerHTML = '';
    const cats = Array.isArray(data.categories) ? data.categories : [];

    for (let i = 0; i < cats.length; i += 2) {
      const row = document.createElement('div');
      row.className = 'po-row';

      [cats[i], cats[i + 1]].forEach(cat => {
        if (!cat) return;
        const cpct = (cat.max > 0) ? (cat.earned / cat.max) * 100 : 0;

        const card = document.createElement('div');
        card.className = 'po-cat';
        card.innerHTML = `
        <div class="po-cat-title">${cat.label}</div>
        <div class="po-cat-bar"><div class="po-cat-fill" style="width:${Math.min(100, cpct).toFixed(2)}%"></div></div>
        <div class="po-cat-legend">
          <span><strong>${cat.earned}</strong> Points Earned</span>
          <span><strong>${cat.max}</strong> Max Points</span>
        </div>`;
        row.appendChild(card);
      });

      holder.appendChild(row);
    }
  })(perfData);
</script>

@endsection
