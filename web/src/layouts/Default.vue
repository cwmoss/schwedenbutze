<template>
  <div id="app">
    <header class="header">
      <div class="header__left">
        <header-logo v-if="showLogo" />
        <navigation />
      </div>

      <div class="header__right">

        <toggle-theme />
      </div>
    </header>
    
    <main class="main">
      <div>
        <slot /> <!-- Default slot -->
      </div>
    </main>

    <footer class="footer">
      <div class="footer__left">
        Copyright © {{ new Date().getFullYear() }} - Powered by Zorro
      </div>

      <div class="footer__right">
          <footer-parts />
      </div>

    </footer>
  </div>
</template>

<script>
import HeaderLogo from '~/components/HeaderLogo'
import ToggleTheme from '~/components/ToggleTheme'
import Navigation from '~/components/Navigation'
import FooterParts from '~/components/FooterParts'

export default {
  props: {
    showLogo: {
      type: Boolean,
      default: false
    }
  },
  components: {
    HeaderLogo,
    ToggleTheme,
    Navigation,
    FooterParts
  }
}
</script>

<style lang="scss">
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: var(--header-height);
  padding: 0 calc(var(--space) / 2);
  top: 0;
  z-index: 10;
  //background-color: var(--bg-color);
  background-color: #fff;

  &__left,
  &__right {
    display: flex;
    align-items: center;
  }

  @media screen and (min-width: 1300px) {
    //Make header sticky for large screens
    position: sticky;
    width: 100%;
  }
}

.main {
  margin: 0 auto;
  padding: 1.5vw 15px 0;
}

.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: calc(var(--space) / 2);
  //text-align: center;
  font-size: 0.8em;

  > span {
    margin: 0 0.35em;
  }

  a {
    color: currentColor;
  }

  &__left,
  &__right {
    display: flex;
    align-items: center;
  }
}
</style>
