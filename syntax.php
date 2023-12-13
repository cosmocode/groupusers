<?php
/**
 * Syntax Plugin:
 * 
 * This plugin lists all users from the given groups in a tabel.
 * Syntax: {{groupusers[|nomail]>group1[,group2[,group3...]]}}
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Dominik Eckelmann <eckelmann@cosmocode.de>
 */

use dokuwiki\Extension\SyntaxPlugin;

class syntax_plugin_groupusers extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    function connectTo($mode) {
         $this->Lexer->addSpecialPattern('\{\{groupusers\>[^}]*?\}\}',$mode,'plugin_groupusers');
         $this->Lexer->addSpecialPattern('\{\{groupusers\|nomail\>[^}]*?\}\}',$mode,'plugin_groupusers');
    }

    function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match,13,-2);
        $data = [];

		if (substr($match, 0, 7) == 'nomail>') {
            $match = substr($match, 7);
            $data['nomail'] = true;
		}
        
        $data['grps'] = explode(',', $match);
		return $data;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $auth;
        global $lang;

        if (!method_exists($auth,"retrieveUsers")) return false;

        if($mode != 'xhtml') return false;

        $users = array();
        if (empty($data['grps'])) $data['grps'] = $data[0]; // ensures backward compatibility to cached data
        foreach ($data['grps'] as $grp) {
            $grp = trim($grp);
            $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
            $users = array_merge($users,$getuser);
        }

        $renderer->doc .= '<table class="inline">';
        $renderer->doc .= '<tr>';
        $renderer->doc .= '<th>'.$lang['user'].'</th>';
        $renderer->doc .= '<th>'.$lang['fullname'].'</th>';
        
        if (empty($data['nomail'])) {
            $renderer->doc .= '<th>'.$lang['email'].'</th>';
        }

        $renderer->doc .= '</tr>';
        foreach ($users as $user => $info) {
            $renderer->doc .= '<tr>';
            $renderer->doc .= '<td>'.htmlspecialchars($user).'</td>';
            $renderer->doc .= '<td>'.hsc($info['name']).'</td>';

            if (empty($data['nomail'])) {
                $renderer->doc .= '<td>';
                $renderer->emaillink($info['mail']);
                $renderer->doc .= '</td>';
            }

            $renderer->doc .= '</tr>';
        }
        $renderer->doc .= '</table>';
        return true;
    }
}
