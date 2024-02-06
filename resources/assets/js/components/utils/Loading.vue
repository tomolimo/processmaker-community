<template>
  <div id='loading'></div>
</template>

<script>
export default {
    name: "Loading"
  
};
</script>

<style lang="scss">
#loading {
  position: fixed;
  left: 0px;
  top: 0px;
  width: 100%;
  height: 100%;
  z-index: 9999;
  background: url(/images/gears.gif) 50% 50% no-repeat rgba(255, 255, 255, 1);
}
</style>